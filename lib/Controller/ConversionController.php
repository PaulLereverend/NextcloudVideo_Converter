<?php
namespace OCA\Video_Converter\Controller;
include __DIR__.'/../vendor/autoload.php';

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use \OCP\IConfig;
use FFMpeg;
use FFMpeg\Media;
use FFProbe;
use FFMpeg\Driver;

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
    public function convertHere($nameOfFile, $directory, $external, $type) {
		$ffmpeg = FFMpeg\FFMpeg::create();
		$format = new FFMpeg\Format\Video\X264();
		if ($external){
			$externalUrl = $this->getExternalMP();
			foreach ($externalUrl as $url) {
				$video = $ffmpeg->open($url.$directory.'/'.$nameOfFile);
				try {
					 $video->save($format, $url.$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);
					 echo "ok";
					 return;
				} catch (ExecutionFailureException $th) {
					echo $th;
				}

			}
			echo "ko";
		}else{
				$video = $ffmpeg->open($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile);
				try {
					$video->save($format, $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'//'.pathinfo($nameOfFile)['filename'].'.'.$type);
					self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);					
				
				} catch (ExecutionFailureException $th) {
					echo $th;
				}
		}
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