<?php
namespace OCA\Video_Converter\Controller;

//require __DIR__ .'/vendor/autoload.php';
//require_once __DIR__ . '/composer/autoload_real.php';
include __DIR__.'/../vendor/autoload.php';

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
//use \OC\Files\Cache\Scanner;
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

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */

    public function convertHere($nameOfFile, $directory, $external, $type) {
		$ffmpeg = FFMpeg\FFMpeg::create();
		$format = new FFMpeg\Format\Video\X264();
		if ($external){
			$good = false;
			$externalUrl = $this->config->getSystemValue('external', '');
			for ($i=0; $i < sizeof($externalUrl) && !$good && $externalUrl[$i]!= null; $i++){
				echo $externalUrl[$i].$directory.'/'.$nameOfFile;
				$video = $ffmpeg->open($externalUrl[$i].$directory.'/'.$nameOfFile);
				try {
					$test = $video->save($format, $externalUrl[$i].$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);
					echo $test;
				} catch (ExecutionFailureException $th) {
					echo $th;
				}
				$good = true;
			}
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