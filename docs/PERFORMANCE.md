# Performance Analysis & Sizing Guide
## Flipped-Microlearning MOOC Platform (FMMP)

This document establishes the sizing models, growth projections, networking throughput bounds, and caching topologies to support MOOC scalability under peak concurrent loads.

*   **Global Architecture:** [ARCHITECTURE.md](file:///d:/xamp/htdocs/fitcoch/docs/ARCHITECTURE.md)
*   **Modular Details:** [MODULES.md](file:///d:/xamp/htdocs/fitcoch/docs/MODULES.md)
*   **Database Specifications:** [DATABASE.md](file:///d:/xamp/htdocs/fitcoch/DATABASE.md)

---

## 1. Concurrency & Latency Budgets

### 1.1 Target Concurrency
*   **Annual Active Learners (Total Registered):** 500,000
*   **Daily Active Users (DAU):** 50,000 (10% of total)
*   **Peak Concurrent Users (CCU):** 10,000 (20% of DAU)
*   **Single Cohort WebRTC Concurrency:** Max 10,000 participants in a single live flipped classroom (using selective media forwarding where only the instructor's video is streamed to students).

### 1.2 Target Latency Budgets (95th Percentile)
| Transaction Type | Threshold | Metric Target |
| :--- | :--- | :--- |
| **API Endpoints (Read)** | `< 150 ms` | Fetching progress, profile stats, or reviews. |
| **API Endpoints (Write)** | `< 200 ms` | Updating heartbeats or submitting quiz choices. |
| **Video Stream Latency (First Frame)**| `< 1.5 sec` | Byte-range header responses starting output. |
| **WebSockets Broadcast (Live Poll)** | `< 250 ms` | Poll distributions across 10,000 users. |
| **Page Render (Tailwind/CSS Web)** | `< 2.0 sec` | Server-Side Rendered views on mobile 3G networks. |

---

## 2. Database Growth Forecasting

Let's estimate the MySQL database growth over 1 year, assuming **500,000 active users** and an average of **2 course enrollments per user**.

### 2.1 Storage Allocation per Entity Type

```
500,000 Users Sizing Forecast (Total: ~22.45 GB)
┌────────────────────────────────────────────────────────┐
│ Activity Logs / Heartbeats (12.50 GB)          55.7%  │
├───────────────────────────────┬────────────────────────┤
│ Spaced Rep Schedules (3.75 GB)│ 16.7%                  │
├───────────────────────────────┼────────────────────────┤
│ Quiz Progress (3.00 GB)       │ 13.4%                  │
├───────────────────────────────┼────────────────────────┤
│ Quiz Attempts (2.50 GB)       │ 11.1%                  │
├───────────────────────────────┴────────────────────────┤
│ Profiles & Core Indexes (0.70 GB) 3.1%                 │
└────────────────────────────────────────────────────────┘
```

1.  **User Profiles & Credentials (`users`, `user_roles`):**
    *   *Row Size:* ~1.4 KB (including security hashes and timezone attributes).
    *   *Calculation:* $500,000\text{ users} \times 1.4\text{ KB} = 700\text{ MB}$.
2.  **Course Enrollments (`cohort_enrollments`):**
    *   *Row Size:* ~200 Bytes.
    *   *Calculation:* $1,000,000\text{ enrollments} \times 200\text{ bytes} = 200\text{ MB}$.
3.  **Nugget Progress Tracking (`nugget_progress`):**
    *   *Syllabus Size:* Average 20 nuggets per course. Total 40 nuggets completed per user.
    *   *Row Size:* ~150 Bytes.
    *   *Calculation:* $500,000\text{ users} \times 40\text{ items} \times 150\text{ bytes} = 3.0\text{ GB}$.
4.  **Quiz Attempts & Choices (`quiz_attempts`, `quiz_responses`):**
    *   *Quiz Count:* 5 readiness quizzes per course (10 quizzes completed per user). Average 5 questions per quiz.
    *   *Row Size:* ~100 Bytes per response option.
    *   *Calculation:* $500,000\text{ users} \times 10\text{ quizzes} \times 5\text{ answers} \times 100\text{ bytes} = 2.5\text{ GB}$.
5.  **Spaced Repetition Items (`spaced_rep_schedules`):**
    *   *Schedules Size:* 50 concepts tracked for review per user.
    *   *Row Size:* ~150 Bytes.
    *   *Calculation:* $500,000\text{ users} \times 50\text{ schedules} \times 150\text{ bytes} = 3.75\text{ GB}$.
6.  **Activity Logs Streams (Heartbeats & Streak updates):**
    *   *Volume:* Average 100 actions recorded per user.
    *   *Row Size:* ~250 Bytes.
    *   *Calculation:* $500,000\text{ users} \times 100\text{ actions} \times 250\text{ bytes} = 12.5\text{ GB}$.

### 2.2 Total Storage Projections
*   **Total Year-1 DB Size:** **~22.45 GB** (highly manageable on MySQL 8 with standard SSD hardware).
*   **Database Archiving Rule:** Move activity logs older than 90 days out of MySQL tables and store them in object storage (AWS S3) to keep active tables small.

---

## 3. Video Streaming Throughput

Microlearning nuggets are short videos (average 5 minutes) encoded at a standard 720p HD resolution (target bitrate: 1.2 Mbps).

### 3.1 Media Storage Sizing
*   *Single Video Nugget Size:*
    $$5\text{ minutes} \times 60\text{ seconds} \times 1.2\text{ Mbps} = 360\text{ Mb} = 45\text{ MB}$$
*   *Syllabus Storage (1 Course of 20 nuggets):*
    $$20\text{ nuggets} \times 45\text{ MB} = 900\text{ MB (under 1 GB)}$$

### 3.2 Concurrent Network Bandwidth Demand
During peak hours, up to 10,000 concurrent users are streaming video nuggets simultaneously.
*   *Peak Network Throughput Requirement:*
    $$10,000\text{ streams} \times 1.2\text{ Mbps} = 12\text{ Gbps (Gigabits per second)}$$

> [!CAUTION]
> Direct streaming of this volume from a single application origin node (XAMPP/PHP host) is impossible. All video delivery must be offloaded to CDN edge caching.

*   **Edge Cache Strategy:** Cache video chunk assets (`.ts` or `.mp4` chunks) on CDN edge nodes (Cloudflare/CloudFront) targeting a **98%+ edge cache hit ratio**, reducing origin network load from 12 Gbps to under **240 Mbps**.

---

## 4. Multi-Tier Caching Topology

To minimize database queries under load, we implement a multi-tiered caching topology.

```
                  ┌──────────────────────────────┐
                  │      Client Browser          │
                  │  (Static templates cache)    │
                  └──────────────┬───────────────┘
                                 │ HTTP Request
                                 ▼
                  ┌──────────────────────────────┐
                  │         CDN Edge             │
                  │    (Videos / HTML views)     │
                  └──────────────┬───────────────┘
                                 │ HTTP Cache Miss
                                 ▼
                  ┌──────────────────────────────┐
                  │    Memory Cache (Redis)      │
                  │   (Sessions / active tasks)  │
                  └──────────────┬───────────────┘
                                 │ Database Cache Miss
                                 ▼
                  ┌──────────────────────────────┐
                  │       MySQL Database         │
                  │     (Primary Datastore)      │
                  └──────────────────────────────┘
```

1.  **Client-Side Browser Cache:** Standardize headers (`Cache-Control: public, max-age=31536000`) for CSS styles and client-side scripts.
2.  **CDN Edge Cache:** Cache video media streams and course outlines. Invalidates cache tags when curriculum updates occur.
3.  **In-Memory Server Cache (Redis Cluster):**
    *   *Volatile Data:* User session models (lifetime: 30 minutes).
    *   *Locks:* Rate-limiting buckets, WebRTC room signals, live class poll counts.
    *   *Queues:* User spaced repetition items generated daily.

---

## 5. Database Indexing Strategy

To speed up query execution times at scale, we use targeted index structures.

1.  **Daily Review Queue Lookup (`spaced_rep_schedules`):**
    *   *Primary Query:* `SELECT * FROM spaced_rep_schedules WHERE user_id = :user_id AND next_review_date <= :today`
    *   *Index:* Composite index on `(user_id, next_review_date)`.
2.  **Cohort Readiness Index Checks (`readiness_tickets`):**
    *   *Primary Query:* `SELECT COUNT(*), status FROM readiness_tickets WHERE cohort_id = :cohort_id AND module_id = :module_id GROUP BY status`
    *   *Index:* Composite index on `(cohort_id, module_id, status)`.
3.  **Active Student Enrollment Validation (`cohort_enrollments`):**
    *   *Primary Query:* `SELECT 1 FROM cohort_enrollments WHERE cohort_id = :cohort_id AND user_id = :user_id AND status = 'active'`
    *   *Index:* Implicit primary key composite `(cohort_id, user_id)` works instantly.

---

## 6. Pagination Models

### 6.1 Offset Pagination (`LIMIT` / `OFFSET`)
*   *Usage:* Only used for administrative pages or lists with low page depth (e.g. course catalogs).
*   *Limitations:* Database scans row offsets sequentially. If offset values are large (e.g. page 500), query performance slows down.

### 6.2 Cursor-Based Pagination (Key Set)
*   *Usage:* Enforced on high-throughput list endpoints (e.g. active event logs, leaderboard listings).
*   *Implementation:* Instead of offsets, client requests supply the last observed record primary key:
    ```sql
    -- Query fetching the next page instantly using index bounds
    SELECT * FROM xp_transactions 
    WHERE user_id = :user_id AND id < :last_seen_id 
    ORDER BY id DESC LIMIT 20;
    ```
*   *Performance:* Remains constant ($O(1)$ search complexity) regardless of database page depth.

---

## 7. Optimization Action Strategy

### 7.1 Materialize Complex Analytics Summaries
Do not execute heavy aggregation queries (e.g., calculations calculating user progress averages) dynamically on page load. Instead, compute summaries asynchronously via CRON schedules, saving output calculations directly to cache variables (Redis) or dedicated summary tables.

### 7.2 Database Table Partitioning
Partition large tables (e.g., partitioning `xp_transactions` and activity logs by Date range) so the database engine only scans relevant partitions during queries.

### 7.3 Asynchronous Queue Processing
When high-frequency tracking actions execute (e.g., video heartbeat logs), write progress data to an in-memory queue (Redis Queue) instead of executing immediate MySQL updates. A background worker process handles bulk updates to the database in batches, minimizing write lock overhead.
