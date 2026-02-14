<?php
require_once '../config/db.php';

// 1. Get Request ID
$requestId = $_GET['id'] ?? 0;

if (!$requestId) {
    die("Invalid Request ID");
}

// 2. Fetch Data
$query = "SELECT cr.id, cr.status, cr.completed_date, cr.verification_code,
                 u.name, s.reg_no, s.discipline, s.cnic, s.father_name, s.dob
          FROM clearance_requests cr
          JOIN students s ON cr.student_id = s.id
          JOIN users u ON s.user_id = u.id
          WHERE cr.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$requestId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data || $data['status'] !== 'completed') {
    die("Certificate not available or clearance incomplete.");
}

// 3. Create Image Canvas (Landscape A4-ish ratio: 1200x850)
$width = 1200;
$height = 850;
$image = imagecreatetruecolor($width, $height);

// 4. Define Colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 30, 30, 30);
$gold = imagecolorallocate($image, 218, 165, 32);
$darkBlue = imagecolorallocate($image, 0, 51, 102);
$grey = imagecolorallocate($image, 128, 128, 128);
$lightGrey = imagecolorallocate($image, 240, 240, 240);

// 5. Background & Border
imagefill($image, 0, 0, $white);

// WATERMARK LOGIC
$logoPath = '../logo.gif';
if (file_exists($logoPath)) {
    $logo = imagecreatefromgif($logoPath);
    if ($logo) {
        $logoW = imagesx($logo);
        $logoH = imagesy($logo);
        // Center the watermark
        $dstX = ($width - $logoW) / 2;
        $dstY = ($height - $logoH) / 2;
        // Merge with low opacity (20%)
        imagecopymerge($image, $logo, $dstX, $dstY, 0, 0, $logoW, $logoH, 15);
        imagedestroy($logo);
    }
}

// Ornamental Border (Triple Line)
$borderPadding = 20;
imagesetthickness($image, 5);
imagerectangle($image, $borderPadding, $borderPadding, $width-$borderPadding, $height-$borderPadding, $darkBlue);
imagesetthickness($image, 2);
imagerectangle($image, $borderPadding+10, $borderPadding+10, $width-$borderPadding-10, $height-$borderPadding-10, $gold);
imagerectangle($image, $borderPadding+20, $borderPadding+20, $width-$borderPadding-20, $height-$borderPadding-20, $darkBlue);

// 6. Header Section
// University Title
$fontScale = 5; 
// Centering helper
function centerText($img, $size, $y, $text, $color, $width) {
    $fontWidth = imagefontwidth($size);
    $textWidth = $fontWidth * strlen($text);
    $x = ($width - $textWidth) / 2;
    imagestring($img, $size, $x, $y, $text, $color);
}

// Header Background (Transparent/Light) - Removed solid block to show logo
// imagefilledrectangle($image, $borderPadding+25, 50, $width-$borderPadding-25, 180, $lightGrey);

centerText($image, 5, 80, "UNIVERSITY OF KUST", $darkBlue, $width);
centerText($image, 4, 110, "Excellence in Education", $black, $width);
centerText($image, 5, 140, "OFFICIAL CLEARANCE CERTIFICATE", $gold, $width);

// 7. Certificate Body
$baseY = 250;
$lineHeight = 50;

// "This is to certify..."
centerText($image, 5, $baseY, "This is to certify that", $black, $width);

// Content Box
$boxX = 200;
$boxY = $baseY + 60;
$valueX = $boxX + 350;

function printRow($img, $y, $label, $value, $lblColor, $valColor) {
    imagestring($img, 5, 250, $y, $label, $lblColor);
    imagestring($img, 5, 550, $y, ":  " . strtoupper($value), $valColor);
}

printRow($image, $boxY + 20,  "Name of Student",     $data['name'], $black, $darkBlue);
printRow($image, $boxY + 70,  "Father's Name",       $data['father_name'], $black, $darkBlue);
printRow($image, $boxY + 120, "Registration No",     $data['reg_no'], $black, $darkBlue);
printRow($image, $boxY + 170, "CNIC Number",         $data['cnic'] ?? 'N/A', $black, $darkBlue);
printRow($image, $boxY + 220, "Department",          $data['discipline'], $black, $darkBlue);
printRow($image, $boxY + 270, "Date of Clearance",   date("d F, Y", strtotime($data['completed_date'])), $black, $darkBlue);

// 8. Footer & Verification
$footerY = 700;

// Signature Line
imageline($image, 800, $footerY, 1050, $footerY, $black);
imagestring($image, 5, 870, $footerY + 10, "Controller of Examinations", $black);
imagestring($image, 3, 900, $footerY + 30, "(Digitally Approved)", $grey);

// Verification Code
$vCode = $data['verification_code'] ?? 'PENDING';

// Verification Box
imagefilledrectangle($image, 100, $footerY-20, 500, $footerY+80, $lightGrey);
imagestring($image, 5, 120, $footerY, "VERIFICATION CODE", $darkBlue);
imagestring($image, 4, 120, $footerY + 30, $vCode, $black);
imagestring($image, 2, 120, $footerY + 55, "Verify at: portal.university.edu/verify", $grey);

// 9. Output
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="Certificate_'.$data['reg_no'].'.png"');

imagepng($image);
imagedestroy($image);
?>
