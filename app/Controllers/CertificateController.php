<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\CertificateService;
use Exception;

class CertificateController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CertificateService $certificateService,
    ) {
    }

    public function show(Request $request, string $hash): Response
    {
        $details = $this->certificateService->getVerificationDetails($hash);

        if ($details === null) {
            return Response::view('errors/forbidden', [
                'title' => __('certificates.not_found_title'),
            ], 404);
        }

        $user = $this->authService->currentUser();
        $roles = $user !== null ? $this->authService->getUserRoles($user->id) : [];

        return Response::view('certificates/show', [
            'title' => __('certificates.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'showSidebar' => $user !== null,
            'certificate' => $details['certificate'],
            'learner' => $details['user'],
            'course' => $details['course'],
            'badges' => $details['badges'],
            'verificationUrl' => url('/certificate/' . $hash),
            'downloadUrl' => url('/certificate/' . $hash . '/download'),
        ]);
    }

    public function download(Request $request, string $hash): Response
    {
        $details = $this->certificateService->getVerificationDetails($hash);

        if ($details === null) {
            return Response::view('errors/forbidden', [
                'title' => __('certificates.not_found_title'),
            ], 404);
        }

        return Response::view('certificates/print', [
            'title' => __('certificates.title'),
            'certificate' => $details['certificate'],
            'learner' => $details['user'],
            'course' => $details['course'],
            'badges' => $details['badges'],
            'verificationUrl' => url('/certificate/' . $hash),
        ]);
    }

    public function showForCourse(Request $request, int $courseId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        try {
            $details = $this->certificateService->issueForLearner($user->id, $courseId);
        } catch (Exception $e) {
            return Response::redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }

        if ($details === null) {
            return Response::redirect('/courses/' . $courseId . '?error=certificate_not_eligible');
        }

        return Response::redirect('/certificate/' . $details['certificate']->verificationHash);
    }

    public function apiShow(Request $request, string $hash): Response
    {
        $details = $this->certificateService->getVerificationDetails($hash);

        if ($details === null) {
            return Response::apiError('NOT_FOUND', __('certificates.validation.not_found'), 404);
        }

        $learner = $details['user'];
        $course = $details['course'];
        $certificate = $details['certificate'];

        return Response::apiSuccess([
            'user_name' => trim($learner->firstName . ' ' . $learner->lastName),
            'course_title' => $course->title,
            'awarded_at' => $certificate->awardedAt,
            'verification_hash' => $certificate->verificationHash,
        ]);
    }
}
