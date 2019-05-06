<?php
namespace OCA\Video_Converter\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use \OCP\IConfig;

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
			if ($mount["backend"] == "Local"){
				$externalMountPoints[] = $mount["options"]["datadir"];
			}
		}
		return $externalMountPoints;
	}
	/**
	* @NoAdminRequired
	*/
	public function convertHere($nameOfFile, $directory, $external, $type, $preset, $priority, $override = false, $shareOwner = null) {
		$response = array();
		if ($external){
			$externalUrl = $this->getExternalMP();
			$desc = "";
			foreach ($externalUrl as $url) {
				if (file_exists($url.$directory.'/'.$nameOfFile)){
					$cmd = $this->createCmd($url.$directory.'/',$nameOfFile,$preset,$type, $priority);
					exec($cmd, $output,$return);
					if($return == 127){
						$response = array_merge($response, array("code" => 0, "desc" => "ffmpeg is not installed or available"));
						return json_encode($response);
					}else{
						if ($override == "true"){
							unlink($url.$directory.'/'.$nameOfFile);
						}
						$response = array_merge($response, array("code" => 1));
						return json_encode($response);
					}
				}
				$desc .= $url.$directory.'/'.$nameOfFile." not found ";
			}
			$response = array_merge($response, array("code" => 0, "desc" => "Can't find video on external local storage : ".$desc));
			return json_encode($response);
		}else{
			if ($shareOwner != null){
				$this->UserId = $shareOwner;
			}
			if (file_exists($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile)){
				$cmd = $this->createCmd($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/',$nameOfFile,$preset,$type, $priority);
				exec($cmd, $output,$return);
				if($return == 127){
					$response = array_merge($response, array("code" => 0, "desc" => "ffmpeg is not installed or available"));
					return json_encode($response);
				}
				$scan = self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);
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
			$middleArgs = "-preset ".escapeshellarg($preset);
		}
		$cmd = " ffmpeg -y -i ".escapeshellarg($link.$filename)." ".$middleArgs." ".escapeshellarg($link.pathinfo($filename)['filename'].".".$output);
		if ($priority != "0"){
			$cmd = "nice -n ".escapeshellarg($priority).$cmd;
		}
		return $cmd;
	}
	/**
	* @NoAdminRequired
	*/
	public function scanFolder($path)
    {
		$response = array();
        $user = \OC::$server->getUserSession()->getUser()->getUID();
		$scanner = new \OC\Files\Utils\Scanner($user, \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());

		try {
            $scanner->scan($path, $recusive = false);
        } catch (ForbiddenException $e) {
			$response = array_merge($response, array("code" => 0, "desc" => $e));
			return json_encode($response);
        }catch (NotFoundException $e){
			$response = array_merge($response, array("code" => 0, "desc" => "Can't scan file at ".$path));
			return json_encode($response);
		}catch (\Exception $e){
			$response = array_merge($response, array("code" => 0, "desc" => $e));
			return json_encode($response);
		}
		return 1;
	}
}