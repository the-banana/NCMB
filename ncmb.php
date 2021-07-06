<?php

    class NCMB {
        private $app_key;
        private $client_key;

        protected $fqdn = "mbaas.api.nifcloud.com";
        protected $path = "/2013-09-01/";
        protected $signature_method = "HmacSHA256";
        protected $signature_version = "2";
        protected $protocol = "https://";
        protected $method = [
            "PUT" => "PUT",
            "GET" => "GET"
        ];

        public function __construct(string $app_key, string $client_key) {
            $this->app_key = $app_key;
            $this->client_key = $client_key;
            
        }

        public function update(string $path, array $params){
            return $this->request("PUT", $path, "", $params);
        }

        public function query(string $path, string $where = ""){
            return $this->request("GET", $path, $where, []);
        }

        private function get_query_string(string $where){
            $hash = [];
            $hash['where'] = $where;
            return http_build_query($hash, null, null, PHP_QUERY_RFC3986);
        }

        private function request(string $method, string $path, string $where, array $params){

            $timestamp = $this->get_timestamp();
            $signature = $this->get_signature($method, $path, $timestamp, $where);
            $ch = curl_init();

            $url = $this->protocol . $this->fqdn . $this->path . "classes/" . $path;
            if(mb_strtoupper($method) === $this->method["GET"] && $where !== '' && $where !== null){
                $url = $url . "?" . $this->get_query_string($where);
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if(mb_strtoupper($method) === $this->method["PUT"] && !empty($params)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            $headers = array();
            $headers[] = "X-NCMB-Application-Key: " . $this->app_key;
            $headers[] = "X-NCMB-Timestamp: " . $timestamp;
            $headers[] = "X-NCMB-Signature: " . $signature;
            $headers[] = "Content-Type: application/json";

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $result =  "{\"error\":\"" . curl_error($ch) . "\"}";
            }
            curl_close ($ch);
            return $result;

        }

        private function get_timestamp(){
            $date = new DateTime();
            return $date->format(DateTime::ISO8601);
        }

        private function get_signature(string $method, string $path, string $timestamp, string $where){

            $signature = "";
            $signature = $signature . "SignatureMethod=" . $this->signature_method;
            $signature = $signature . "&SignatureVersion=" . $this->signature_version;
            $signature = $signature . "&X-NCMB-Application-Key=" . $this->app_key;
            $signature = $signature . "&X-NCMB-Timestamp=" . $timestamp;
            if(!empty($where)){
                $signature = $signature . "&" . $this->get_query_string($where);
            }

            $signature = $method . "\n" . $this->fqdn . "\n" .$this->path . "classes/" . $path . "\n" . $signature;
            return base64_encode(hash_hmac("sha256", $signature, $this->client_key, true));
        }
    }

?>