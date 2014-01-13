<?php 

class Exifdata {

    var $filename;

    public function get_exif($filename) {

        $coordinates = $this->get_coordinates($filename);
        $ifd0 = $this->get_IFD0($filename);

        if ($ifd0) {
            foreach ($ifd0 as $key => $value) {
                $arr[$key] = $value;
            }
        }

        if ($coordinates) {
            $arr['latitude'] = $coordinates[0];
            $arr['longitude'] = $coordinates[1];
            $arr['x'] = $this->coordinate_2dms($coordinates[0], 'N', 'E');
            $arr['y'] = $this->coordinate_2dms($coordinates[1], 'E', 'N');
        }

        return (object) $arr;
    }

    private function exif_to_number($value, $format) {
            $spos = strpos($value, '/');
            if ($spos === false) {
                    return sprintf($format, $value);
            } 
            else {
                    list($base, $divider) = explode("/", $value, 2);

                    if ($divider == 0) return sprintf($format, 0);
                    else return sprintf($format, ($base / $divider));
            }
    }

    private function exif_to_coordinate($reference, $coordinate) {
            if ($reference == 'S' || $reference == 'W') $prefix = '-';
            else $prefix = '';
                    
            return $prefix . sprintf('%.6F', $this->exif_to_number($coordinate[0], '%.6F') +
                    ((($this->exif_to_number($coordinate[1], '%.6F') * 60) +        
                    ($this->exif_to_number($coordinate[2], '%.6F'))) / 3600));
    }

    private function get_coordinates($filename) {
        if (extension_loaded('exif')) {
                    $exif = exif_read_data($filename, 'EXIF');
                    
                    if (isset($exif['GPSLatitudeRef']) && isset($exif['GPSLatitude']) && 
                            isset($exif['GPSLongitudeRef']) && isset($exif['GPSLongitude'])) {
                            return array (
                                    $this->exif_to_coordinate($exif['GPSLatitudeRef'], $exif['GPSLatitude']), 
                                    $this->exif_to_coordinate($exif['GPSLongitudeRef'], $exif['GPSLongitude'])
                            );
                    }
            }
    }

    private function get_IFD0($filename) {
        if (extension_loaded('exif')) {
            $exif = exif_read_data($filename, 'IFD0');
            
            $arr = false;

            if (isset($exif['Make']) && isset($exif['Model'])) {
                $arr['make'] = $exif['Make'];
                $arr['model'] = $exif['Model'];
            }
            if (isset($exif['DateTime'])) {
                $arr['datetime'] = $exif['DateTime'];
            }

            return $arr;
        }
    }

    private function coordinate_2dms($coordinate, $pos, $neg) {
        $sign = $coordinate >= 0 ? $pos : $neg;
        
        $coordinate = abs($coordinate);
        $degree = intval($coordinate);
        $coordinate = ($coordinate - $degree) * 60;
        $minute = intval($coordinate);
        $second = ($coordinate - $minute) * 60;
        
        return sprintf("%s %d&#xB0; %02d&#x2032; %05.2f&#x2033;", $sign, $degree, $minute, $second);
    }
}