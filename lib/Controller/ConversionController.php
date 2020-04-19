<?php
namespace OCA\Video_Converter\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use \OCP\IConfig;
use OCP\EventDispatcher\IEventDispatcher;


class ConversionController extends Controller {
	private $config;
	private $UserId;
	/**
	* @NoAdminRequired
	*/
	public function __construct(IConfig $config, $AppName, IRequest $request, string $UserId){
		parent::__construct($AppName, $request);
		$this->config = $config;
		$this->UserId = $UserId;
	}
	/**
	* @NoAdminRequired
	*/
	public function getExternalMP(){
		$mounts = \OC_Mount_Config::getAbsoluteMountPoints($this->UserId);
		$externalMountPoints = array();
		foreach($mounts as $mount){
			if ($mount["class"] == "local"){
				$externalMountPoints[$mount["mountpoint"]] = $mount["options"]["datadir"];
			}
		}
		return $externalMountPoints;
	}
	/**
	* @NoAdminRequired
	*/
	public function convertHere($nameOfFile, $directory, $external, $type, $preset, $priority, $codec = null, $vbitrate = null, $scale = null, $override = false, $shareOwner = null, $mtime = 0) {
		if (preg_match('/(\/|^)\.\.(\/|$)/', $nameOfFile)) {
			$response = ['code' => 0, 'desc' => 'Can\'t find file'];
			return json_encode($response);
		 }
		 if (preg_match('/(\/|^)\.\.(\/|$)/', $directory)) {
			$response = ['code' => 0, 'desc' => 'Can\'t open file at directory'];
			return json_encode($response);
		 }
		$response = array();
		if ($external){
			$externalUrl = $this->getExternalMP();
			$desc = "";
			$dircpt = substr($directory, 1);
			while ($dircpt != ""){
				if (array_key_exists($dircpt, $externalUrl)){
					$url = $externalUrl[$dircpt];
					$dircpt = str_replace($dircpt, "", $directory);
					if (file_exists($url.'/'.$dircpt.'/'.$nameOfFile)){
						$cmd = $this->createCmd($url.'/'.$dircpt.'/',$nameOfFile,$preset,$type, $priority, $codec, $vbitrate, $scale);
						exec($cmd, $output,$return);
						if($return == 127){
							$response = array_merge($response, array("code" => 0, "desc" => "ffmpeg is not installed or available \n
							DEBUG: ".$url.'/'.$dircpt.'/'.$nameOfFile));
							return json_encode($response);
						}else{
							if ($override == "true"){
								unlink($url.'/'.$directory.'/'.$nameOfFile);
							}
							$response = array_merge($response, array("code" => 1));
							return json_encode($response);
						}
					}else{
						$response = array_merge($response, array("code" => 0, "desc" => "Can't find video on external local storage : ".$url.'/'.$dircpt.'/'.$nameOfFile));
						return json_encode($response);
					}
				}else{
					$pos = strrpos( $dircpt, '/');
					if ($pos == false){
						$dircpt = "/";
					}else{
						$dircpt= substr($dircpt, 0, $pos);
					}
				}
			}
			$response = array_merge($response, array("code" => 0, "desc" => "Can't find video on external local storage"));
			return json_encode($response);
		}else{
			if ($shareOwner != null){
				$this->UserId = $shareOwner;
			}
			if (file_exists($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile)){
				$cmd = $this->createCmd($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/',$nameOfFile,$preset,$type, $priority, $codec, $vbitrate, $scale);
				exec($cmd, $output,$return);
				if($return == 127){
					$response = array_merge($response, array("code" => 0, "desc" => "ffmpeg is not installed or available \n
					 DEBUG: ".$this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile));
					return json_encode($response);
				}
				$scan = self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type, $this->UserId);
				if($scan != 1){
					return $scan;
				}
				$response = array_merge($response, array("code" => 1));
				return json_encode($response);
			}else{
				$response = array_merge($response, array("code" => 0, "desc" => "Can't find video at ".$this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile));
				return json_encode($response);
			}
		}
	}
	/**
	* @NoAdminRequired
	*/
	public function createCmd($link,$filename,$preset,$output, $priority, $codec, $vbitrate, $scale){
		$middleArgs = "";
		if ($output == "webm"){
			switch ($preset) {
				case 'faster':
					$middleArgs = "-vcodec libvpx -cpu-used 1 -threads 16";
					break;
				case 'veryfast':
					$middleArgs = "-vcodec libvpx -cpu-used 2 -threads 16";
					break;
				case 'superfast':
					$middleArgs = "-vcodec libvpx -cpu-used 4 -threads 16";
					break;
				case 'ultrafast':
					$middleArgs = "-vcodec libvpx -cpu-used 5 -threads 16 -deadline realtime";
					break;
				default:
					break;
			}
		}else{
                        if ($codec != null){
                            switch ($codec) {
                                case 'x264':
                                    $middleArgs = "-vcodec libx264 -preset ".escapeshellarg($preset). " -strict -2";
                                    break;
                                case 'x265':
                                    $middleArgs = "-vcodec libx265 -preset ".escapeshellarg($preset). " -strict -2";
                                    break;
                            }
                        } else {
                                $middleArgs = "-preset ".escapeshellarg($preset). " -strict -2";
                        }
                        if ($vbitrate != null) {
                            switch ($vbitrate) {
                                case '1':
                                    $vbitrate = '1000k';
                                    break;
                                case '2':
                                    $vbitrate = '2000k';
                                    break;
                                case '3':
                                    $vbitrate = '3000k';
                                    break;
                                case '4':
                                    $vbitrate = '4000k';
                                    break;
                                case '5':
                                    $vbitrate = '5000k';
                                    break;
                                case '6':
                                    $vbitrate = '6000k';
                                    break;
                                case '7':
                                    $vbitrate = '7000k';
                                    break;
                                default :
                                    $vbitrate = '2000k';
                                    break;
                            }
                            $middleArgs = $middleArgs." -b:v ".$vbitrate;
                        }
                        if ($scale != null) {
                            switch ($scale) {
                                case 'wxga':
                                    $scale = " -vf scale=1280:720";
                                    break;
                                case 'hd':
                                    $scale = " -vf scale=1368:768";
                                    break;
                                case 'fhd':
                                    $scale = " -vf scale=1920:1080";
                                    break;
                                case 'uhd':
                                    $scale = " -vf scale=3840:2160";
                                    break;
                                case '600':
                                    $scale = " -vf scale=-1:600";
                                    break;
                                case '720':
                                    $scale = " -vf scale=-1:720";
                                    break;
                                case '1080':
                                    $scale = " -vf scale=-1:1080";
                                    break;
                                default:
                                    $scale = "";
                                    break;
                            }
                            $middleArgs = $middleArgs.$scale;
                        }
		}
		//echo $link;
		$cmd = " ffmpeg -y -i ".escapeshellarg($link.$filename)." ".$middleArgs." ".escapeshellarg($link.pathinfo($filename)['filename'].".".$output);
		if ($priority != "0"){
			$cmd = "nice -n ".escapeshellarg($priority).$cmd;
		}
		return $cmd;
	}
	/**
	* @NoAdminRequired
	*/
	public function scanFolder($path, $user)
    {
		$response = array();
		/*if($user == null){
			$user = \OC::$server->getUserSession()->getUser()->getUID();
		}*/
		$version = \OC::$server->getConfig()->getSystemValue('version');
		 if((int)substr($version, 0, 2) < 18){
			$scanner = new \OC\Files\Utils\Scanner($user, \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		 }else{
			$scanner = new \OC\Files\Utils\Scanner($user, \OC::$server->getDatabaseConnection(),\OC::$server->query(IEventDispatcher::class), \OC::$server->getLogger());
		 }
		try {
            $scanner->scan($path, $recusive = false);
        } catch (ForbiddenException $e) {
			$response = array_merge($response, array("code" => 0, "desc" => $e->getTraceAsString()));
			return json_encode($response);
        }catch (NotFoundException $e){
			$response = array_merge($response, array("code" => 0, "desc" => $this->l->t("Can't scan file at ").$path));
			return json_encode($response);
		}catch (\Exception $e){
			$response = array_merge($response, array("code" => 0, "desc" => $e->getTraceAsString()));
			return json_encode($response);
		}
		return 1;
	}
}
