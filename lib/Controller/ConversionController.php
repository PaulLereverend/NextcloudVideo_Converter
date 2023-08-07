<?php
namespace OCA\Video_Converter\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use \OCP\IConfig;
use OCP\EventDispatcher\IEventDispatcher;
use OC\Files\Filesystem;


class ConversionController extends Controller {

	private $userId;

	/**
	* @NoAdminRequired
	*/
	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;

	}

	public function getFile($directory, $fileName){
		\OC_Util::tearDownFS();
		\OC_Util::setupFS($this->userId);
		return Filesystem::getLocalFile($directory . '/' . $fileName);
	}
	/**
	* @NoAdminRequired
	*/
	public function convertHere($nameOfFile, $directory, $external, $type, $preset, $priority, $movflags = false, $codec = null, $vbitrate = null, $scale = null, $shareOwner = null, $mtime = 0) {
		$file = $this->getFile($directory, $nameOfFile);
		$dir = dirname($file);
		$response = array();
		if (file_exists($file)){
			$cmd = $this->createCmd($file, $preset, $type, $priority, $movflags, $codec, $vbitrate, $scale);
			exec($cmd, $output,$return);
			// if the file is in external storage, and also check if encryption is enabled
			if($external || \OC::$server->getEncryptionManager()->isEnabled()){
				//put the temporary file in the external storage
				Filesystem::file_put_contents($directory . '/' . pathinfo($nameOfFile)['filename']."_converted.".$type, file_get_contents(dirname($file) . '/' . pathinfo($file)['filename']."_converted.".$type));
				// check that the temporary file is not the same as the new file
				if(Filesystem::getLocalFile($directory . '/' . pathinfo($nameOfFile)['filename']."_converted.".$type) != dirname($file) . '/' . pathinfo($file)['filename']."_converted.".$type){
					unlink(dirname($file) . '/' . pathinfo($file)['filename']."_converted.".$type);
				}
			}else{
				//create the new file in the NC filesystem
				Filesystem::touch($directory . '/' . pathinfo($file)['filename']."_converted.".$type);
			}
			//if ffmpeg is throwing an error
			if($return == 127){
				$response = array_merge($response, array("code" => 0, "desc" => "ffmpeg is not installed or available \n
				DEBUG(".$return."): " . $file . ' - '.$output));
				return json_encode($response);
			}else{
				$response = array_merge($response, array("code" => 1));
				return json_encode($response);
			}
		}else{
			$response = array_merge($response, array("code" => 0, "desc" => "Can't find file at ". $file));
			return json_encode($response);
		}
	}
	/**
	* @NoAdminRequired
	*/
	public function createCmd($file, $preset, $output, $priority, $movflags, $codec, $vbitrate, $scale){
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
		} else {
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

			if ($movflags) {
				$middleArgs = $middleArgs." -movflags +faststart ";
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
					case 'vga':
						$scale = " -vf scale=640:480";
						break;
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
					case '320':
						$scale = " -vf scale=-1:320";
						break;
					case '480':
						$scale = " -vf scale=-1:480";
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
		// I put this here because the code up there seems to be chained in a string builder and I didn't want to disrupt the code too much.
		// This is useful if you just want to change containers types, and do no work with codecs. So you can convert an MKV to MP4 almost instantly.
		if($codec == "copy"){
			$middleArgs = "-codec copy";
		}
		$cmd = " ffmpeg -y -i ".escapeshellarg($file)." ".$middleArgs." ".escapeshellarg(dirname($file) . '/' . pathinfo($file)['filename']."_converted.".$output);
		if ($priority != "0"){
			$cmd = "nice -n ".escapeshellarg($priority).$cmd;
		}
		return $cmd;
	}
}
