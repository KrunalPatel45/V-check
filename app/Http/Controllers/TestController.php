<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Payors;
use App\Models\Checks;
use Illuminate\Support\Facades\Validator;
use PDF;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\File;
use App\Models\Package;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PaymentHistory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TestController extends Controller
{
    public function test()
    {
        return view('user.check_formate.test');
        $check_file = $this->generateAndSavePDF();
    }

    public function generateAndSavePDF()
    {
        $directoryPath = public_path('checks');
        // Create fonts directory if it doesn't exist
        $fontPath = storage_path('fonts');
        if (!File::exists($fontPath)) {
            File::makeDirectory($fontPath, 0755, true);
        }

        // Define font file paths
        $fontFiles = [
            'MICRCheckPrixa.ttf',
            'MICRCheckPrixa.woff',
            'MICRCheckPrixa.woff2',
            'MICRCheckPrixa.eot'
        ];

        // Copy font files from public to storage if they don't exist
        foreach ($fontFiles as $fontFile) {
            $sourcePath = public_path('storage/fonts/' . $fontFile);
            $destPath = $fontPath . '/' . $fontFile;
            
            if (!File::exists($destPath) && File::exists($sourcePath)) {
                File::copy($sourcePath, $destPath);
            }
        }

        // Configure DOMPDF font cache directory
        $configPath = config_path('dompdf.php');
        if (File::exists($configPath)) {
            config(['dompdf.options.font_cache' => $fontPath]);
        }

        // Check if the directory exists, if not, create it
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
        // Generate PDF from a view
        $pdf = PDF::loadView('user.check_formate.test')->setPaper('a4', 'portrait')
        ->setPaper([0, 0, 1000, 1200])
        ->set_option('isHtml5ParserEnabled', true)
        ->set_option('isRemoteEnabled', true);
    
        // Define the file path where you want to save the PDF
        $file_name = 'check-' .'testing' . '.pdf';
        $filePath = $directoryPath .  '/' . $file_name;
    
        // Save the PDF to the specified path
        $pdf->save($filePath);
        return $file_name;
    }

    public function subscription_update()
    {
        $today = Carbon::today()->subDay()->toDateString();
        $subscriptions = PaymentSubscription::whereDate('PaymentStartDate', $today)->where('Status', '!=', 'Canceled')->get();
        foreach($subscriptions as $subscription){
            if($subscription->Status == 'Pending') {
                $user = User::find($subscription->UserID);
                $user->CurrentPackageID = $subscription->PackageID;
                $subscription->Status = 'Active';
                $subscription->save();

                PaymentSubscription::where('UserID', $user->UserID)->where('PaymentSubscriptionID', '!=', $subscription->PaymentSubscriptionID)->delete();


                $paymentSubscription = PaymentHistory::where('PaymentSubscriptionID', $subscription->PaymentSubscriptionID)->where('PaymentStatus','Pending')->first();
                $paymentSubscription->PaymentStatus = 'Success';
                $paymentSubscription->save();
            }
        }

        echo "<pre>";
        echo "Subscription updated";
    }

    public function checkout()
    {
        return view('frontend.checkout');
    }

    public function smtp_checker_view()
    {
        return view('user.mail_check');
    }

    public function smtp_checker(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $smtpHost = env('MAIL_HOST'); 
        $smtpPort = env('MAIL_PORT');  
        $smtpUser = env('MAIL_USERNAME'); 
        $smtpPass = env('MAIL_PASSWORD'); 
        $fromEmail = env('MAIL_FROM_ADDRESS');  
        $toEmail = $request->email; 

        $mail = new PHPMailer(true);

        
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            $mail->setFrom($fromEmail, 'PHPMailer');
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = 'PHPMailer SMTP Test';
            $mail->Body    = 'This is a test email sent using PHPMailer';
            $mail->AltBody = 'This is a test email sent using PHPMailer';

            $mail->send();
            return back()->with('success', 'Email sent successfully to ' . $toEmail);
        } catch (Exception $e) {
            return back()->with('error', 'Mail error: ' . $mail->ErrorInfo);
        }

    }
}
