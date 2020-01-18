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
	public function convertHere($nameOfFile, $directory, $external, $type, $preset, $priority, $override = false, $shareOwner = null, $mtime = 0) {
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
						$cmd = $this->createCmd($url.'/'.$dircpt.'/',$nameOfFile,$preset,$type, $priority);
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
				$cmd = $this->createCmd($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/',$nameOfFile,$preset,$type, $priority);
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
	public function createCmd($link,$filename,$preset,$output, $priority){
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
			$middleArgs = "-preset ".escapeshellarg($preset). " -strict -2";
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