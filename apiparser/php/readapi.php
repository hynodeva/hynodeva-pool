<?php

class coinapistats
{
public $name;
public $difficulty;
public $netspeed;
public $poolspeed;
public $currentshares;
public $lastbtceprice;
function __construct($newname = "default")
{
	$this->name=$newname;
	$this->difficulty=0.0;
	$this->netspeed=0.0;
	$this->poolspeed=0.0;
	$this->currentshares=0;
	$this->lastbtceprice=0.0;
}
public function load($inp)
{
if(array_key_exists("difficulty",$inp))
	$this->difficulty=$inp["difficulty"];
if(array_key_exists("networkhashrate",$inp))
	$this->netspeed=$inp["networkhashrate"];
if(array_key_exists("poolhashrate",$inp))
	$this->poolspeed=$inp["poolhashrate"];
if(array_key_exists("pool_currentshares",$inp))
	$this->currentshares=$inp["pool_currentshares"];
if(array_key_exists("lastprice_at_btce",$inp))
	$this->lastbtceprice=$inp["lastprice_at_btce"];
}


}

class serverapistats
{
public $coins;
public $serverloads;
public $servertime;
public $webserverlag;
function __construct()
{
	$this->coins=array();
	$this->serverloads=array("hashserver"=>0.0,"webserver"=>0.0);
	$this->servertime=0;
	$this->webserverlag=0;
}
public function addcoin(coinapistats $newcoin)
{
	$this->coins[$newcoin->name]=$newcoin;
}
public function loadcoins($inputdata)
{
	foreach($inputdata as $coinname => $coin)
	{
	$tmpcoin=new coinapistats($coinname);
	$tmpcoin->load($coin);
	$this->addcoin($tmpcoin);
	}
}

public function loadserverloads($jsont)
{
if(array_key_exists("serverload",$jsont))
	$this->serverloads["hashserver"]=$jsont["serverload"];
if(array_key_exists("webserverload",$jsont))
	$this->serverloads["webserver"]=$jsont["webserverload"];
if(array_key_exists("webserver_db_lag",$jsont))
	$this->webserverlag=$jsont["webserver_db_lag"];
}

public function load($inputdata)
{
$jsont=json_decode($inputdata, true);
if(array_key_exists("coins",$jsont))
	$this->loadcoins($jsont["coins"]);
$this->loadserverloads($jsont);
}

}

class hynodeva_dot_com_api extends serverapistats
{
	public $datatime;
	function __construct()
	{
		parent::__construct();
		$inp="";
		$inp=file_get_contents('https://hynodeva.com/api.php');
		$this->datatime=time();
		$this->load($inp);
	}
	public function getcoinvalue($coinname,$valuename)
	{
		if(array_key_exists($coinname, $this->coins))
			return $this->coins[$coinname]->$valuename;
	}
	public function getallcoinsashtml()
	{
		$ret="Coins:<br>";
		$vals=array("netspeed"=>"current net speed","poolspeed"=>"current pool speed","difficulty"=>"current difficulty","currentshares"=>"current pool shares","lastbtceprice"=>"last price at btc-e");
		foreach($this->coins as $coinname => $coin)
			foreach($vals as $valid=>$valtext)
			{
				$ret=$ret.strval($coinname)." $valtext : ".$this->getcoinvalue($coinname,$valid)."<br>";
			}
		return $ret;
	}
	public function getserverdataashtml()
	{
		$ret="Server:<br>";
		foreach($this->serverloads as $servername => $load)
		{
			$ret=$ret.strval($servername)." : ".$load." %<br>";
		}
		$ret=$ret."webserver data lag ".$this->webserverlag." s<br>";
		return $ret;
	}
	public function getfullhtml()
	{
		$ret="time : ".date("r",$this->datatime)."<br>";
		$ret=$ret.$this->getserverdataashtml();
		$ret=$ret.$this->getallcoinsashtml();
		return $ret;
	}
}

?>