<?php
/**
 * Securimage CAPTCHA Library (SVG-based, no GD required)
 * Simplified version for basic CAPTCHA functionality
 */

class Securimage {
    
    private $code = '';
    private $code_length = 6;
    private $image_width = 250;
    private $image_height = 80;
    private $session_name = 'securimage_code';
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Generate CAPTCHA code and store in session
     */
    public function generate() {
        // Generar código aleatorio
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Sin I, O, 0, 1
        $this->code = '';
        
        for ($i = 0; $i < $this->code_length; $i++) {
            $this->code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        
        // Guardar en sesión
        $_SESSION[$this->session_name] = strtolower($this->code);
        $_SESSION[$this->session_name . '_time'] = time();
        
        return $this->code;
    }
    
    /**
     * Draw CAPTCHA image as SVG
     */
    public function display() {
        header('Content-Type: image/svg+xml; charset=UTF-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Generar código
        $code = $this->generate();
        
        // Crear SVG
        $svg = $this->generateSVG($code);
        
        echo $svg;
    }
    
    /**
     * Generate SVG with CAPTCHA text and noise
     */
    private function generateSVG($code) {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $this->image_width . '" height="' . $this->image_height . '" viewBox="0 0 ' . $this->image_width . ' ' . $this->image_height . '">' . "\n";
        
        // Fondo blanco
        $svg .= '<rect width="100%" height="100%" fill="white" stroke="#e0e0e0" stroke-width="2"/>' . "\n";
        
        // Agregar líneas de ruido
        for ($i = 0; $i < 5; $i++) {
            $x1 = mt_rand(0, $this->image_width);
            $y1 = mt_rand(0, $this->image_height);
            $x2 = mt_rand(0, $this->image_width);
            $y2 = mt_rand(0, $this->image_height);
            $opacity = mt_rand(5, 15) / 100;
            
            $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" ';
            $svg .= 'stroke="#cccccc" stroke-width="1" opacity="' . $opacity . '"/>' . "\n";
        }
        
        // Agregar puntos de ruido
        for ($i = 0; $i < 30; $i++) {
            $cx = mt_rand(5, $this->image_width - 5);
            $cy = mt_rand(5, $this->image_height - 5);
            $opacity = mt_rand(3, 10) / 100;
            $size = mt_rand(1, 2);
            
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $size . '" ';
            $svg .= 'fill="#d0d0d0" opacity="' . $opacity . '"/>' . "\n";
        }
        
        // Escribir texto
        $textX = 25;
        $textSpacing = 30;
        $baseY = 55;
        
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $x = $textX + ($i * $textSpacing);
            $y = $baseY + mt_rand(-8, 8);
            $rotation = mt_rand(-20, 20);
            
            $svg .= '<text x="' . $x . '" y="' . $y . '" ';
            $svg .= 'font-family="Arial, sans-serif" font-size="36" font-weight="bold" ';
            $svg .= 'fill="' . $this->getRandomColor() . '" ';
            $svg .= 'transform="rotate(' . $rotation . ' ' . $x . ' ' . $y . ')" ';
            $svg .= 'text-anchor="middle">' . htmlspecialchars($char) . '</text>' . "\n";
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Get random color for text
     */
    private function getRandomColor() {
        $colors = [
            '#1a1a1a',
            '#0033cc',
            '#cc0000',
            '#006600',
            '#663300',
            '#9900cc',
            '#ff6600',
        ];
        
        return $colors[array_rand($colors)];
    }
    
    /**
     * Check user input against stored code
     */
    public static function check($user_input) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $session_name = 'securimage_code';
        $session_time = 'securimage_code_time';
        
        // Verificar que existe el código en sesión
        if (!isset($_SESSION[$session_name])) {
            return false;
        }
        
        // Verificar que no expiró (10 minutos)
        if (isset($_SESSION[$session_time]) && (time() - $_SESSION[$session_time] > 600)) {
            unset($_SESSION[$session_name]);
            unset($_SESSION[$session_time]);
            return false;
        }
        
        // Comparar (case-insensitive)
        $is_valid = strtolower(trim($user_input)) === strtolower($_SESSION[$session_name]);
        
        // Limpiar después de validar (una sola vez)
        if ($is_valid) {
            unset($_SESSION[$session_name]);
            unset($_SESSION[$session_time]);
        }
        
        return $is_valid;
    }
    
    /**
     * Set code length
     */
    public function setCodeLength($length) {
        $this->code_length = (int)$length;
    }
    
    /**
     * Set image dimensions
     */
    public function setImageSize($width, $height) {
        $this->image_width = (int)$width;
        $this->image_height = (int)$height;
    }
}
?>
