<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetOtpController extends Controller
{
    public function __construct(private EmailService $emailService)
    {
    }

    /**
     * Show the password reset request form
     */
    public function showRequestForm(): Response
    {
        return Inertia::render('auth/ForgotPasswordOtp');
    }

    /**
     * Send OTP for password reset
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        try {
            // Generate OTP
            $otpRecord = PasswordResetOtp::generateOtp($email);
            
            // Send OTP email
            $emailSent = $this->emailService->sendPasswordResetOtpEmail(
                $user, 
                $otpRecord->otp,
                $otpRecord->expires_at->format('g:i A')
            );

            if ($emailSent) {
                return back()->with('status', 'We have sent an OTP to your email address.');
            } else {
                return back()->withErrors(['email' => 'Failed to send OTP. Please try again.']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Show the OTP verification form
     */
    public function showOtpForm(Request $request): Response
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('password.request.otp');
        }

        return Inertia::render('auth/ResetPasswordOtp', [
            'email' => $email
        ]);
    }

    /**
     * Verify OTP and show password reset form
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $otp = $request->otp;

        // Find valid OTP
        $otpRecord = PasswordResetOtp::findValidOtp($email, $otp);

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP. Please request a new one.'])->withInput();
        }

        // Mark OTP as used
        $otpRecord->markAsUsed();

        // Store email in session for password reset
        session(['password_reset_email' => $email]);

        return redirect()->route('password.reset.otp.form');
    }

    /**
     * Show the password reset form
     */
    public function showResetForm(): Response
    {
        $email = session('password_reset_email');
        
        if (!$email) {
            return redirect()->route('password.request.otp');
        }

        return Inertia::render('auth/NewPasswordOtp', [
            'email' => $email
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $password = $request->password;

        // Update user password
        $user = User::where('email', $email)->first();
        $user->update([
            'password' => Hash::make($password)
        ]);

        // Clear session
        session()->forget('password_reset_email');

        return redirect()->route('login')->with('status', 'Your password has been reset successfully. You can now login with your new password.');
    }
} 