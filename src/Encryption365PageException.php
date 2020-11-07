<?php
namespace TrustOcean\Encryption365;
use Throwable;
use TrustOcean\Encryption365\Common\TwigUtils;

class Encryption365PageException extends \Exception{

    public function __construct($title="", $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $twig = TwigUtils::initTwig();
        die($twig->render('pageException.html.twig',['title'=>$title,'message'=>$message,'code'=>$code]));
    }
}