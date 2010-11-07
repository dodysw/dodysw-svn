<?
    function get_canberra_temp() {
    /*
    Get Canberra "it feels like" temperature in Celcius from weather.com. URL taken from The Weather 2.5 Widget Konfabulator.
    Dody Suria Wijaya <dodysw@gmail.com> 8aug05
    */
        $url = 'http://xoap.weather.com/weather/local/ASXX0023?cc=*&dayf=6&prod=xoap&link=xoap&par=1003725713&key=1729016d019d4a7d&unit=m';
        ob_start();
        @readfile($url);
        $buffer = ob_get_contents();
        ob_end_clean();
        if ($buffer != '' and preg_match('#<flik>(\d+)</flik>#is', $buffer, $group)) return $group[1];
        return FALSE;
    }

    //example of use
    $temperature = get_canberra_temp();
    if ($temperature === FALSE) echo 'Unable to get temperature information. Please check back later.';
    else echo 'Currently is '.$temperature.' degree Celcius';
?>