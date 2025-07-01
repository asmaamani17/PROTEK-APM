<?php
// Create colored markers for different statuses
$markers = [
    'mohon_bantuan' => [255, 59, 48],     // Red
    'dalam_tindakan' => [255, 149, 0],    // Orange
    'sedang_diselamatkan' => [0, 122, 255], // Blue
    'bantuan_selesai' => [52, 199, 89]    // Green
];

foreach ($markers as $name => $color) {
    $width = 32;
    $height = 32;
    
    // Create a blank image
    $image = imagecreatetruecolor($width, $height);
    
    // Make the background transparent
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    imagesavealpha($image, true);
    
    // Draw a circle
    $color = imagecolorallocate($image, $color[0], $color[1], $color[2]);
    imagefilledellipse($image, $width/2, $height/2, $width-2, $height-2, $color);
    
    // Add a white border
    $white = imagecolorallocate($image, 255, 255, 255);
    imageellipse($image, $width/2, $height/2, $width-2, $height-2, $white);
    
    // Save the image
    $filename = __DIR__ . "/marker-{$name}.png";
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Generated: $filename\n";
}

echo "All marker images have been generated.\n";
?>
