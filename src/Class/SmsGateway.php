<?php

class SmsGateway {

    public static function SendSms($data, $body) {
        $message = $data['body'];
        $message = str_replace('{{nama}}', $body['nama'], $message);
        $message = str_replace('{{jumlah_pembelian}}', $body['jumlah_pembelian'], $message);

        $payloads = array(
            'apiusername' => 'APIDMR231QSQJ',
            'apipassword' => 'APIDMR231QSQJJ8AUB',
            'mobileno' => $body['mobile_no'], 
            'senderid' => $body['sender'], 
            'message' =>  $message
        );
        $cURL = curl_init();
        curl_setopt( $cURL, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($cURL, CURLOPT_URL, INTEGRATION_GATEWAY . '/onewaysms/send-mt');
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($payloads));
        $resp = curl_exec($cURL);
        curl_close($cURL);
        return $resp;
    }

}
