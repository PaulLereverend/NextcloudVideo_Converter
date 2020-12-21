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
	/**
	* @NoAdminRequired
	*/
	public function __construct($AppName, IRequest $request){
		parent::__construct($AppName, $request);
	}

	public function getFile($directory, $fileName){
		return Filesystem::getLocalFile($directory . '/' . $fileName);
	}
	/**
	* @NoAdminRequired
	*/
	public function convertHere($nameOfFile, $directory, $external, $type, $preset, $priority, $codec = null, $vbitrate = null, $scale = null, $shareOwner = null, $mtime = 0) {
		$file = $this->getFile($directory, $nameOfFile);
		$dir = dirname($file);
		$response = array();
		if (file_exists($file)){
			$cmd = $this->createCmd($file,$preset,$type, $priority, $codec, $vbitrate, $scale);
			exec($cmd, $output,$return);
			Filesystem::touch($directory . '/' . pathinfo($file)['filename'].".".$type);
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
	public function createCmd($file,$preset,$output, $priority, $codec, $vbitrate, $scale){
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
		$cmd = " ffmpeg -y -i ".escapeshellarg($file)." ".$middleArgs." ".escapeshellarg(dirname($file) . '/' . pathinfo($file)['filename'].".".$output);
		if ($priority != "0"){
			$cmd = "nice -n ".escapeshellarg($priority).$cmd;
		}
		return $cmd;
	}
}
