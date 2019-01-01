<?php
namespace Yeates\Visitor\Services;


class IpHandleService{
    const RESOLVE_IP_CHANNEL = ['TaoBaoIpChannel','FreeIpChannel','CzIpChannel'];
    const RESOLVE_IP_URL = [
        'TaoBaoIpChannel'=>"http://ip.taobao.com/service/getIpInfo.php?ip=[IP]",
        'FreeIpChannel'=>"http://freeapi.ipip.net/[IP]",
        'CzIpChannel'=>'../Datas/CzData.dat'
    ];
    public $country = '';
    public $region = '';
    public $city = '';
    public $isp = '';
    public $ip = '';
    public $channel = '';
    
    public function handle($ip){
        $channel = self::RESOLVE_IP_CHANNEL;
        $handleData = [];
        try{
            array_walk($channel,function($channel)use($ip,&$handleData){
                $channelClass = app($channel);
                $ipInfo = array_filter($channelClass->get($ip));
                
                if($ipInfo){
                    if(count($ipInfo) >= 3){
                        $handleData[0]=$ipInfo;
                        throw new \Exception(json_encode($handleData));
                    }else{
                        $handleData[count($ipInfo)]=$ipInfo;
                    }
                }
            });
        }catch (\Exception $e){
            $handleData = json_decode($e->getMessage(),true);
        }
        $data = end($handleData);
        $this->city = $data['city'];
        $this->isp = $data['isp'];
        $this->ip = $data['ip'];
        $this->region = $data['region'];
        $this->country = $data['country'];
        $this->channel = $data['channel'];
        return $this;
        
    }
    
    public function toArray(){
        return [
            'city'=>$this->city,
            'isp'=>$this->isp,
            'ip'=>$this->ip,
            'region'=>$this->region,
            'country'=>$this->country
        ];
    }
    
    public static function save(IpHandleService $that){
        $data = $that->toArray();
        $visitorIpPool = VisitorIpPool::create($data);
        return $visitorIpPool->id;
    }
}

