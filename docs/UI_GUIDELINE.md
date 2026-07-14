# User Interface Design Guideline & Screen Specifications
## Flipped-Microlearning MOOC Platform (FMMP)

This document establishes the UI/UX design tokens, layout strategies, component designs, and responsive viewport specifications using Tailwind CSS classes for both Light and Dark mode.

*   **Global Architecture:** [ARCHITECTURE.md](file:///d:/xamp/htdocs/fitcoch/docs/ARCHITECTURE.md)
*   **Module Breakdown:** [MODULES.md](file:///d:/xamp/htdocs/fitcoch/docs/MODULES.md)
*   **Database Specifications:** [DATABASE.md](file:///d:/xamp/htdocs/fitcoch/DATABASE.md)

---

## 1. Global UI Design Tokens & Theme

To ensure a premium, modern, and engaging user experience, we utilize a design system with smooth gradients, glassmorphism, micro-animations, and curated color palettes.

### 1.1 Color Palettes
| Token | Light Mode Class | Dark Mode Class | Usage |
| :--- | :--- | :--- | :--- |
| **Primary (Brand)** | `bg-indigo-600 text-white` | `dark:bg-indigo-500` | CTA buttons, active state highlights |
| **Secondary** | `bg-emerald-600` | `dark:bg-emerald-500` | Success banners, completed items, unlocked gates |
| **Background (Base)** | `bg-slate-50` | `bg-slate-950` | Full screen backdrops |
| **Background (Card)** | `bg-white/80 backdrop-blur-md`| `dark:bg-slate-900/80` | Cards, dashboards, overlays (Glassmorphism) |
| **Border** | `border-slate-200` | `dark:border-slate-800` | Section dividers and outline borders |
| **Text Primary** | `text-slate-900` | `dark:text-slate-100` | Headings, core body readouts |
| **Text Secondary**| `text-slate-500` | `dark:text-slate-400` | Sub-titles, captions, metadata tags |

### 1.2 Micro-Animations & Transitions
*   **Standard Hover:** `transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-lg`
*   **Click State:** `active:scale-[0.98] transition-transform`
*   **Loading State:** `animate-pulse` or custom spinner rotations.

---

## 2. Student Portal Screens

### 2.1 Screen 1: Gamified Student Dashboard (`/dashboard`)
*   **Layout:** Multi-column layout. A left-hand sidebar navigates page directories. The main center viewport displays active daily items, and the right-hand panel houses social rankings (Leaderboards) and badges.
*   **Components:**
    1.  *Streak Counter Card:* Displays current daily streak with a flame icon and a progress track.
    2.  *XP Tracker:* Circular ring progress charting XP earned toward the next Level.
    3.  *Daily Spaced-Rep Card:* A prominent visual call-to-action button alerting that the daily reinforcement review is ready.
    4.  *Enrolled Courses Checklist:* Horizontal cards representing active courses with progression bars.
*   **User Flow:**
    *   *Step 1:* Learner logs in and lands on the dashboard.
    *   *Step 2:* Clicks the Spaced-Rep card to enter the review queue (redirects to `/review/daily`).
    *   *Step 3:* Scrolls enrolled courses list and clicks a course to view its syllabus (redirects to `/courses/{id}`).
*   **Tailwind UI Code Structure:**
    ```html
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex">
      <!-- Sidebar -->
      <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 p-6 flex flex-col justify-between hidden md:flex">
        <!-- Sidebar Navigation Elements -->
      </aside>
      
      <!-- Main Content -->
      <main class="flex-1 p-6 md:p-10 overflow-y-auto">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Streak Flame Card (Glassmorphism) -->
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4 transition-all hover:scale-[1.01]">
            <div class="p-3 bg-orange-100 dark:bg-orange-950/50 rounded-xl text-orange-500">
              <!-- Flame SVG Icon -->
            </div>
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Current Streak</p>
              <h3 class="text-2xl font-bold">14 Days</h3>
            </div>
          </div>
          <!-- XP Card -->
          <!-- Daily Spaced-Rep Callout -->
        </div>
      </main>
    </div>
    ```
*   **Responsive Adaptation:** On mobile viewports, the side navigation collapses into a bottom navigation bar, and the multi-column layout stacks vertically.
*   **Dark Mode Styling:** Active dark utility overrides: `dark:bg-slate-950`, `dark:bg-slate-900/80`, `dark:border-slate-800`, `dark:text-slate-100`.

---

### 2.2 Screen 2: Course Syllabus & Sequence Outline (`/courses/{course_id}`)
*   **Layout:** Two-column split layout. The left column lists syllabus modules with an accordion navigation. The right column shows details of the selected module, including unlocked and locked nuggets.
*   **Components:**
    1.  *Module Accordion Row:* Displays completion checkmarks, module titles, and duration expectations.
    2.  *Progress Tracking Track:* A vertical timeline indicating unlocked microlearning steps (videos, cards) and the locked Flipped Readiness Gate (quiz icon with a lock).
    3.  *Gate Status Alert:* A prominent card indicating if the live session ticket is locked, unlocked, or manually overridden.
*   **User Flow:**
    *   *Step 1:* Learner selects a module from the accordion checklist.
    *   *Step 2:* Clicks an active unlocked nugget node to consume content (opens `/nuggets/{nugget_id}`).
    *   *Step 3:* Once all prep nuggets are checked, the system unlocks the Readiness Quiz card.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Module Outline Accordion Row -->
    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden mb-4 bg-white/50 dark:bg-slate-900/50">
      <button class="w-full flex items-center justify-between p-5 font-semibold text-left">
        <span class="flex items-center gap-3">
          <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">✓</span>
          Module 1: Foundations of Microlearning
        </span>
        <span class="text-sm text-slate-500">4 / 4 Nuggets</span>
      </button>
    </div>
    ```
*   **Responsive Adaptation:** On mobile screens, the module details view slides in as a overlay sheet when a module row is tapped.
*   **Dark Mode Styling:** Cards transition to semi-transparent black surfaces (`dark:bg-slate-900/50`) with borders using slate highlights (`dark:border-slate-800`).

---

### 2.3 Screen 3: Learning Nugget Viewer (`/nuggets/{nugget_id}`)
*   **Layout:** Structured split-view layout. The left pane hosts the content player (video container or reading scroll space), and the right pane shows secondary resources, interactive notes, and progress milestones.
*   **Components:**
    1.  *HTML5 Custom Video Player:* Houses video streaming with timeline track tracking, volume bar, scrub indicators, and play toggle.
    2.  *Text Reading Card:* Rich-text typography card container utilizing readable fonts.
    3.  *Progress Track Indicator:* Top-pinned linear progress bar displaying completion status (e.g. 75% watched).
*   **User Flow:**
    *   *Step 1:* Learner enters screen; the progress bar starts tracked.
    *   *Step 2:* Plays the video nugget or scrolls down the text card.
    *   *Step 3:* The video player submits playback progress to `VideoController` on 10-second heartbeats.
    *   *Step 4:* Upon reaching 90% progress, a transition banner signals completion and reveals the "Next Nugget" button.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Progress Indicator Bar -->
    <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
      <div class="h-full bg-indigo-600 transition-all duration-500" style="width: 75%;"></div>
    </div>
    
    <!-- Video Player Frame -->
    <div class="relative aspect-video rounded-2xl overflow-hidden bg-black border border-slate-200 dark:border-slate-800 shadow-xl group">
      <video class="w-full h-full object-cover" src="/video/42/stream"></video>
      <!-- Custom Overlay HUD Controls -->
      <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 to-transparent p-4 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-between">
        <!-- Control buttons -->
      </div>
    </div>
    ```
*   **Responsive Adaptation:** Landscape rotation on mobile devices triggers automatic full-screen video mode, shifting reading text elements below the media container.
*   **Dark Mode Styling:** Video containers use solid black backdrops (`bg-black`) while typography containers switch to off-white slate text (`dark:text-slate-200`).

---

### 2.4 Screen 4: Pre-Class Readiness Quiz Screen (`/quiz/{quiz_id}`)
*   **Layout:** Focused single-card interface designed to minimize cognitive load. The top header shows progress counters, and the central body displays the question and options.
*   **Components:**
    1.  *Question Card:* Large bold question texts container.
    2.  *Options Cards Group:* Vertical list of selectable card options.
    3.  *Timer Progress Indicator:* A thin linear bar tracking countdown values.
    4.  *Feedback Overlay Dialog:* Red (Incorrect) and Green (Correct) bottom popups appearing after submit.
*   **User Flow:**
    *   *Step 1:* User enters the quiz screen and views the first question.
    *   *Step 2:* Selects an option card (highlights card border in primary indigo color).
    *   *Step 3:* Clicks "Submit Answer".
    *   *Step 4:* System displays visual feedback. Clicking "Next" animates transition to the next question card.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Option Selection Card (Interactive) -->
    <button class="w-full flex items-center p-4 border-2 rounded-xl text-left transition-all border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
      <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 font-semibold flex items-center justify-center mr-4">A</span>
      <span class="font-medium">Space complexity is O(N) auxiliary space.</span>
    </button>
    ```
*   **Responsive Adaptation:** Tap targets are scaled up on mobile screens for easy touch interaction.
*   **Dark Mode Styling:** Focus rings utilize transparent indigo overlays (`dark:ring-indigo-500/20`), and correct choices use green borders (`dark:border-emerald-500`).

---

### 2.5 Screen 5: Virtual Synchronous Classroom (`/live/{room_id}`)
*   **Layout:** WebRTC active media layout. The left column shows the main video feed, the right column houses the agenda tracker and class chat, and the bottom contains interaction controls.
*   **Components:**
    1.  *Active Video Panels:* Responsive grid showing the instructor's video feed and screen share.
    2.  *Agenda Tracker:* A timeline list checking off in-class schedule topics (e.g. Q&A, Poll, Breakout).
    3.  *Live Poll Overlay:* Pop-up overlay card displaying live instructor polls.
    4.  *Participant Readiness Badges:* Indicator icon overlays showing who completed the pre-class prep.
*   **User Flow:**
    *   *Step 1:* Learner joins the room (validated by `readiness_ticket`).
    *   *Step 2:* Watches the live presentation feed.
    *   *Step 3:* When the instructor launches a poll, an overlay slides onto the screen.
    *   *Step 4:* Learner submits an option, and the overlay updates to show aggregate class responses.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Live Poll Overlay Card -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <h4 class="text-lg font-bold mb-4">In-Class Live Poll</h4>
        <!-- Question and options list -->
      </div>
    </div>
    ```
*   **Responsive Adaptation:** Chat panels collapse into a slide-over panel on mobile screens, giving maximum space to the main video stream.
*   **Dark Mode Styling:** Semi-transparent backdrops switch to dark gray layers (`dark:bg-slate-950/60`) and overlay frames match container borders (`dark:border-slate-800`).

---

### 2.6 Screen 6: Spaced Repetition Daily Review (`/review/daily`)
*   **Layout:** Minimalist screen featuring card deck styles.
*   **Components:**
    1.  *Flashcard Deck Frame:* Double-sided card structure that supports flip animations.
    2.  *Front Side:* Question/concept name.
    3.  *Back Side:* Detailed answer text and explanation blocks.
    4.  *Recall Rating Buttons:* Quality ratings from 0 to 5 (Blackout, Incorrect, Hesitant, Correct, Easy).
*   **User Flow:**
    *   *Step 1:* Card shows a concept prompt.
    *   *Step 2:* Learner tests recall and clicks "Reveal Answer" (flips card with a 3D rotate transition).
    *   *Step 3:* Learner selects a rating button (0 to 5) based on their memory recall effort.
    *   *Step 4:* System updates SM-2 intervals and triggers a sliding animation to load the next card.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Quality Rating Buttons Group -->
    <div class="grid grid-cols-5 gap-2 mt-6">
      <button class="p-3 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 font-bold flex flex-col items-center">
        <span>0</span>
        <span class="text-[10px] font-normal mt-1">Forgot</span>
      </button>
      <!-- Buttons 1, 2, 3, 4, 5 -->
    </div>
    ```
*   **Responsive Adaptation:** Interactive rating buttons expand to stretch across full-width stacked layouts on narrow screens.
*   **Dark Mode Styling:** Card containers use `dark:bg-slate-900`, and rating buttons map to dark variants (e.g. red buttons map to `dark:bg-red-950/30 dark:border-red-900/50 dark:text-red-400`).

---

### 2.7 Screen 7: Certificate of Mastery Screen (`/certificate/{hash}`)
*   **Layout:** Clean, minimalist single-page layout displaying the certificate card.
*   **Components:**
    1.  *Mastery Certificate Frame:* Custom card with gold borders, watermark background, and elegant typography.
    2.  *Badge Icons Grid:* Displays earned course badges and credentials.
    3.  *Action Panel:* Buttons to copy verification link, share to social channels, and trigger PDF downloads.
*   **User Flow:**
    *   *Step 1:* Learner completes the course and unlocks the certificate screen.
    *   *Step 2:* Clicks "Download PDF" (triggers print format layouts).
    *   *Step 3:* Copies the verification link to share credentials.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Certificate Border Frame (Print Safe CSS) -->
    <div class="max-w-4xl mx-auto border-8 border-amber-500/25 p-8 bg-white dark:bg-slate-900 rounded-3xl relative overflow-hidden shadow-2xl print:border-amber-500 print:shadow-none">
      <div class="border-2 border-amber-500/20 p-8 rounded-2xl flex flex-col items-center text-center">
        <!-- Certificate contents: Seals, signatures, user name, course title -->
      </div>
    </div>
    ```
*   **Responsive Adaptation:** The certificate frame scales down using container queries to fit smaller viewport screen dimensions.
*   **Dark Mode Styling:** In dark mode, card backdrops display slate colors (`dark:bg-slate-900`), but print utility media rules enforce a white backdrop during PDF print compilation.

---

## 3. Admin & Instructor Portal Screens

### 3.1 Screen 8: Course Creator & Editor (`/instructor/courses`)
*   **Layout:** Dual pane split view. The left side is a drag-and-drop course module syllabus builder. The right side is a rich details content editor.
*   **Components:**
    1.  *Syllabus Builder Tree:* Drag-and-drop hierarchy tree (Modules ➔ Nuggets).
    2.  *Content Editor Form:* Input fields for titles, descriptions, types (video, card, quiz), and file uploads.
    3.  *Progressive File Uploader Dropzone:* Interactive drag-and-drop box for videos and attachments.
*   **User Flow:**
    *   *Step 1:* Instructor clicks "+ Add Module" or creates a nugget row.
    *   *Step 2:* Drags a nugget row to reorganize its sequence order.
    *   *Step 3:* Uploads a video nugget using the file dropzone (visual upload progress tracking matches upload status).
    *   *Step 4:* Clicks "Save changes".
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Drag and Drop Content Row -->
    <div class="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 cursor-grab active:cursor-grabbing mb-2">
      <div class="flex items-center gap-3">
        <span class="text-slate-400"><!-- Drag Grip Icon --></span>
        <span class="font-medium">Nugget 1: Streamlining Video Playbacks</span>
      </div>
      <!-- Edit/Delete Actions -->
    </div>
    ```
*   **Responsive Adaptation:** Reorganizing content uses full-screen modal sheets on mobile viewports.
*   **Dark Mode Styling:** Drag-and-drop container handles use slate gray tones (`dark:bg-slate-800`) and border dividers map to dark styles (`dark:border-slate-700`).

---

### 3.2 Screen 9: Cohort Readiness & Analytics Monitor (`/instructor/analytics/cohort/{cohort_id}`)
*   **Layout:** Two-column analytics view. The left side shows aggregate cohort statistics (readiness indices, misconceptions). The right side lists individual student ticket states with filter tools.
*   **Components:**
    1.  *Aggregate Readiness Metrics Card:* Displays overall preparation progress against the 60% alert threshold.
    2.  *Misconception Priority List:* Lists quiz questions with highest incorrect response rates.
    3.  *At-Risk Students Table:* Lists students who missed pre-class prep for multiple sessions.
    4.  *Manual Overwrite Toggle:* Instant override button to manually unlock tickets for selected students.
*   **User Flow:**
    *   *Step 1:* Instructor accesses the dashboard before class.
    *   *Step 2:* Checks the readiness metric card. If completion is below 60%, a red warning indicator appears.
    *   *Step 3:* Views the misconception panel to adjust the live class agenda.
    *   *Step 4:* Manually overrides ticket locks for late-preparing students.
*   **Tailwind UI Code Structure:**
    ```html
    <!-- Readiness Warning Card (BR-04 Trigger) -->
    <div class="p-6 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-2xl flex items-start gap-4">
      <div class="p-3 bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-400 rounded-xl font-bold">!</div>
      <div>
        <h4 class="font-bold text-red-800 dark:text-red-400">Low Readiness Warning</h4>
        <p class="text-sm text-red-700 dark:text-red-500 mt-1">Cohort preparation rate is at 45% (threshold is 60%).</p>
      </div>
    </div>
    ```
*   **Responsive Adaptation:** Large data tables transition into responsive lists with expandable detail accordions on mobile screens.
*   **Dark Mode Styling:** Error alerts style with subtle red backdrops (`dark:bg-red-950/20`) and warning text uses muted red hues (`dark:text-red-400`).
