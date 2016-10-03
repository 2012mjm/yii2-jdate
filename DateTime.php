<?php

namespace mjm\jdate;

use yii\base\InvalidParamException;
/**
 * Jalali date & time.
 * @author Mohammad Mahdi Gholomian.
 * @copyright 2014 mm.gholamian@yahoo.com
 * @developed by Mohammad Javad Masoudian <info@mjm3d.ir>
 */
class DateTime
{
	function date($dateFormat, $timeStamp = false)
	{
		if(! is_string($dateFormat))
		{
			throw new InvalidParamException(
				"Date format is inavlid! " .
				"This must be a string."
			);
		}

		$dateTime = new \DateTime('@' . ($timeStamp ?: time()),  new \DateTimeZone('Asia/Tehran'));
		$dateTime->setTimeZone(new \DateTimeZone('Asia/Tehran'));
		list($jalaliYear, $jalaliMonth, $jalaliDay) = $this->toJalaliDate(
			$year	= $dateTime->format('Y'),
			$month 	= $dateTime->format('m'),
			$day 	= $dateTime->format('d'));
		
		$res = '';
		$skipNextCahr = false;
		for ($i = 0; $i < strlen($dateFormat); $i++) {
			$char = $dateFormat[$i];
			if ($skipNextCahr) {
				$skipNextCahr = false;
				$res .= $char;
				continue;
			}
			switch ($char) {
				case 'A' :
						if($dateTime->format('a') == 'pm') {
							$res .= 'بعد از ظهر';
						} else {
							$res .= 'قبل از ظهر';
						}
					break;
				case 'a' :
						if($dateTime->format('a') == 'pm') {
							$res .= 'ب.ظ';
						} else {
							$res .= 'ق.ظ';
						}
					break;
				case 'd':
					if ($jalaliDay < 10) {
						$res .= '0' . $jalaliDay;
					} else {
						$res .= $jalaliDay;
					}
					break;
				case 'D':
					$res .= $this->getDayShortName($dateTime->format('D'));
					break;
				case 'F':
					$res .= $this->getMonthName($jalaliMonth);
					break;
				case 'g':
					$res .= $dateTime->format('g');
					break;
				case 'G':
					$res .= $dateTime->format('G');
					break;
				case 'h':
					$res .= $dateTime->format('h');
					break;
				case 'H':
					$res .= $dateTime->format('H');
					break;
				case 'i':
					$res .= $dateTime->format('i');
					break;
				case 'j':
					$res .= $jalaliDay;
					break;
				case 'l':
					$res .= $this->getDayName($dateTime->format('l'));
					break;
				case 'm':
					if ($jalaliMonth < 10) {
						$res .= '0' . $jalaliMonth;
					} else {
						$res .= $jalaliMonth;
					}
					break;
				case 'M':
					$res .= $this->getShortMonthName($jalaliMonth);
					break;
				case 'n':
					$res .= $jalaliMonth;
					break;
				case 's':
					$res .= $dateTime->format('s');
					break;
				case 'S':
					$res .= 'م';
					break;
				case 't':
					$res .= $this->daysInMonth($month, $day, $year);
					break;
				case 'w':
					$res .= $dateTime->format('w');
					break;
				case "y":
					$res .= substr($jalaliYear, 2);
					break;
				case 'o':
				case "Y":
					$res .= $jalaliYear;
					break;
				case "\\" :
					$skipNextCahr = true;
					break;
				default:
					$res .= $char;
					break;
			}
		}

		return $res;
	}

	function __get($var)
	{
		return $this->date($var);
	}

	function toJalaliDate($g_y, $g_m, $g_d) 
	{

		$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
		$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);      
		$gy = $g_y-1600; 
		$gm = $g_m-1; 
		$gd = $g_d-1; 
		$g_day_no = 365*$gy+$this->div($gy+3,4)-$this->div($gy+99,100)+$this->div($gy+399,400); 
		for ($i=0; $i < $gm; ++$i) 
			$g_day_no += $g_days_in_month[$i]; 

		if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0))) 
			/* leap and after Feb */ 
			$g_day_no++; 
		$g_day_no += $gd; 
		$j_day_no = $g_day_no-79; 
		$j_np = $this->div($j_day_no, 12053); /* 12053 = 365*33 + 32/4 */ 
		$j_day_no = $j_day_no % 12053; 
		$jy = 979+33*$j_np+4*$this->div($j_day_no,1461); /* 1461 = 365*4 + 4/4 */ 
		$j_day_no %= 1461; 
		if ($j_day_no >= 366) { 

		  $jy += $this->div($j_day_no-1, 365); 

		  $j_day_no = ($j_day_no-1)%365; 

		} 
		for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) 
			$j_day_no -= $j_days_in_month[$i]; 

		$jm = $i+1;
		$jd = $j_day_no+1; 
		return array($jy, $jm, $jd); 

	}

    /**
     * Jalali to Gregorian Conversion
     * Copyright (C) 2000  Roozbeh Pournader and Mohammad Toossi
     * Edited by Mohammad Javad Masoudian <info@mjm3d.ir>
     */
    public function toGregorianDate($j_y, $j_m, $j_d)
    {

        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

        $jy = $j_y-979;
        $jm = $j_m-1;
        $jd = $j_d-1;

        $j_day_no = 365*$jy + $this->div($jy, 33)*8 + $this->div($jy%33+3, 4);
        for ($i=0; $i < $jm; ++$i)
            $j_day_no += $j_days_in_month[$i];

        $j_day_no += $jd;

        $g_day_no = $j_day_no+79;

        $gy = 1600 + 400*$this->div($g_day_no, 146097);
        $g_day_no = $g_day_no % 146097;

        $leap = true;
        if ($g_day_no >= 36525) {
            $g_day_no--;
            $gy += 100*$this->div($g_day_no,  36524);
            $g_day_no = $g_day_no % 36524;

            if ($g_day_no >= 365)
                $g_day_no++;
            else
                $leap = false;
        }

        $gy += 4*$this->div($g_day_no, 1461);
        $g_day_no %= 1461;

        if ($g_day_no >= 366) {
            $leap = false;

            $g_day_no--;
            $gy += $this->div($g_day_no, 365);
            $g_day_no = $g_day_no % 365;
        }

        for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++)
            $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
        $gm = $i+1;
        $gd = $g_day_no+1;

        return array($gy, $gm, $gd);

    }

	function div($a,$b) {
		return (int) ($a / $b);
	}

	function getDayNames()
	{
		return [
			'saturday' => 'شنبه',
			'sunday' => 'یکشنیه',
			'monday' => 'دوشنبه',
			'tuesday' => 'سه شنبه',
			'wednesday' => 'چهارشنبه',
			'thursday' => 'پنجشنبه',
			'friday' => 'جمعه',
			
		];
	}
	function getDayShortNames()
	{
		return [
			'sat' => 'ش',
			'sun' => 'ی',
			'mon' => 'د',
			'tue' => 'س',
			'wed' => 'چ',
			'thu' => 'پ',
			'fri' => 'ج',
		];
	}

	function getDayName($day)
	{
		$day = strtolower($day);
		return isset($this->getDayNames()[$day]) ? $this->getDayNames()[$day] : false;
	}
	function getDayShortName($day)
	{
		$day = strtolower($day);
		return isset($this->getDayShortNames()[$day]) ? $this->getDayShortNames()[$day] : false;
	}
	function getMonthNames()
	{
		return [
			1 => 'فروردین',
			2 => 'اردیبهشت',
			3 => 'خرداد',
			4 => 'تیر',
			5 => 'مرداد',
			6 => 'شهریور',
			7 => 'مهر',
			8 => 'آبان',
			9 => 'آذر',
			10 => 'دی',
			11 => 'بهمن',
			12 => 'اسفند',
		];
	}

	function getShortMonthNames()
	{
		return [
			1 => 'فرو',
			2 => 'ارد',
			3 => 'خرد',
			4 => 'تیر',
			5 => 'مرد',
			6 => 'شهر',
			7 => 'مهر',
			8 => 'آبا',
			9 => 'آذر',
			10 => 'دی',
			11 => 'بهم',
			12 => 'اسف',
		];
	}

	function getMonthName($month)
	{
		if ($month < 1 || $month > 12) {
			throw new InvalidParamException(
				"Invalid month number. "
			);
		}

		$month = (int) $month;
		return $this->getMonthNames()[$month];
	}

	function getShortMonthName($month)
	{
		if ($month < 1 || $month > 12) {
			throw new InvalidParamException(
				"Invalid month number. "
			);
		}

		$month = (int) $month;
		return $this->getMonthNames()[$month];
	}
	/**
	 * Find numbers in month.
	 */
	function daysInMonth($month,$day,$year)
	{
		$jday2  = "";
		$jdate2 = "";
		$lastdayen = date("d",mktime(0,0,0,$month+1,0,$year));
		list($jyear, $jmonth, $jday) = $this->toJalaliDate($year, $month, $day);
		$lastdatep=$jday;
		$jday=$jday2;
		while ($jday2!="1") {
			if ($day<$lastdayen) {
				$day++;
				list($jyear, $jmonth, $jday2) = $this->toJalaliDate($year, $month, $day);
				if($jdate2=="1") break;
				if($jdate2!="1") $lastdatep++;
			} else {
				$day=0;
				$month++;
				if ($month==13) {
					$month="1";
					$year++;
				}
			}

		}
		return $lastdatep-1;
	}

	/**
     * Creates a Unix Timestamp (Epoch Time) based on given parameters
     * works like php's built in mktime() function.
     * e.g:
     * $time = $obj->mktime(0,0,0,2,10,1368);
     * $obj->date("Y-m-d", $time); //Format and Display
     * $obj->date("Y-m-d", $time, false, false); //Display in Gregorian !
     *
     * You can force gregorian mktime if system default is jalali and you
     * need to create a timestamp based on gregorian date
     * $time2 = $obj->mktime(0,0,0,12,23,1989, false);
     *
     * @author Sallar Kaboli
     * @edited by Mohammad Javad Masoudian <info@mjm3d.ir>
     * @param $hour int Hour based on 24 hour system
     * @param $minute int Minutes
     * @param $second int Seconds
     * @param $month int Month Number
     * @param $day int Day Number
     * @param $year int Four-digit Year number eg. 1390
     * @param $jalali bool (Optional) pass false if you want to input gregorian time
     * @param $timezone string (Optional) acceps an optional timezone if you want one
     * @return int Unix Timestamp (Epoch Time)
     */
    public function mktime($hour, $minute, $second, $month, $day, $year, $jalali = true, $timezone = 'Asia/Tehran')
    {
        //Defaults
        $month = (intval($month) == 0) ? $this->date('m') : $month;
        $day   = (intval($day)   == 0) ? $this->date('d') : $day;
        $year  = (intval($year)  == 0) ? $this->date('Y') : $year;

        //Convert to Gregorian if necessary
        if ( $jalali === true ) {
            list($year, $month, $day) = $this->toGregorianDate($year, $month, $day);
        }

        //Create a new object and set the timezone if available
        $date = $year.'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day).' '.$hour.':'.$minute.':'.$second;

        if ( $timezone != null ) {
            $obj = new \DateTime($date, new \DateTimeZone($timezone));
        }
        else {
            $obj = new \DateTime($date);
        }

        //Return
        return $obj->format("U");
    }
}