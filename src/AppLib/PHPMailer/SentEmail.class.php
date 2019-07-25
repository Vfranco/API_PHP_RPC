<?php

/* ~ SentEmail.class.php
  .---------------------------------------------------------------------------.
  |  Software: PHPMailer - PHP email class                                    |
  | ------------------------------------------------------------------------- |
  |  Author: Carlos Velasquez                                   |
  |  Copyright  2010 Bftrust         			                                |
  | ------------------------------------------------------------------------- |
  |   License: Distributed under the Lesser General Public License (LGPL)     |
  ' ------------------------------------------------------------------------- '
 */

class SentEmail extends PHPMailer
{


    /**
     * Enviar email utilizando la Class PHPMailer.
     * @param $arrayPost Datos que se van a enviar
     * @param $UrlTemplate URL de la Plantilla para el email
     * @param bool $fromName
     * @param bool $subject
     * @param bool $from
     * @return int|string
     */
    public function SentEmailPost($arrayPost, $UrlTemplate, $fromName = false, $subject = false, $from = false)
    {
        $response = 0;
        $body = $this->getFile($UrlTemplate);
        //$body = preg_replace('"', "'", $body);
        //$body = preg_replace("[\]", '', $body);

        while (list($a, $b) = each($arrayPost)) {
            $body = preg_replace("{" . $a . "}", $arrayPost[$a], $body);
            //$body = print_r($ArrayPost,true);
        }
        //$body = @eregi_replace("{URLLINK}", _HOST_NAME, $body);

        if ($from == false) {
            $this->From = "no-reply@volgamanting.com";
        } else {
            $this->From = $from;
        }


        $this->FromName = $fromName;
        $this->Subject = $subject;
        $this->MsgHTML($body);
        $this->AddAddress($arrayPost['emailTo']);


        //$this->AddCC("deivisvb@hotmail.com");
        //$this->AddCC("tech@cparking.com.co");


        //$this->AddCC('person1@domain.com', 'Person One');
        //$this->AddCC('person2@domain.com', 'Person Two');


        if ($this->Send()) {
            $response = 1;
        } else {
            $response = $this->ErrorInfo;
        }


        return $response;

    }

}
