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
	public function __construct(IConfig $config, $AppName, IRequest $request, string $UserId){
		parent::__construct($AppName, $request);
		$this->config = $config;
		$this->UserId = $UserId;
	}

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
    public function convertHere($nameOfFile, $directory, $external, $type, $preset) {
		if ($external){
			$externalUrl = $this->getExternalMP();
			foreach ($externalUrl as $url) {
				echo $url.$directory.'/'.$nameOfFile;
				if (file_exists($url.$directory.'/'.$nameOfFile)){
					$cmd = $this->createCmd($url.$directory.'/',$nameOfFile,$preset,$type);
					exec($cmd);
					return;
				}
			}
			echo "ko";
		}else{
			echo $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile;
			if (file_exists($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile)){
				$cmd = $this->createCmd($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/',$nameOfFile,$preset,$type);
				exec($cmd);
				self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);
			}else{
				echo "ko";
			}					
		}
	}

	public function createCmd($link,$filename,$preset,$output){
		$cmd = "ffmpeg -y -i '".$link.$filename."' -preset ".$preset." '".$link.pathinfo($filename)['filename'].".".$output."'";
		echo $cmd;
		return $cmd;
	}
	protected function scanFolder($path)
    {
		$user = \OC::$server->getUserSession()->getUser()->getUID();
        $scanner = new \OC\Files\Utils\Scanner($user, \OC::$server->getDatabaseConnection(), \OC::$server->getLogger());
		try {
            $scanner->scan($path, $recusive = false);
        } catch (ForbiddenException $e) {
			echo $e;
        } catch (\Exception $e) {
			echo $e;
        }
	} 
}