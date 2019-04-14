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
	private $percentageTrans = 0;
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

    public function convertHere($nameOfFile, $directory, $external, $type, $override) {
		$this->test($nameOfFile,$directory, $external, $type, $override);
	}
	public function getPercentage(){
		echo $_SESSION["newsession"];
	}
	public function test($nameOfFile, $directory, $external, $type, $override){
		$ffmpeg = FFMpeg\FFMpeg::create();
		$format = new FFMpeg\Format\Video\X264();
		session_start();
		$_SESSION["newsession"] = 0;
		if ($external){
			$good = false;
			$externalUrl = $this->config->getSystemValue('external', '');
			for ($i=0; $i < sizeof($externalUrl) && !$good && $externalUrl[$i]!= null; $i++){
				echo $externalUrl[$i].$directory.'/'.$nameOfFile;
				$video = $ffmpeg->open($externalUrl[$i].$directory.'/'.$nameOfFile);

				$format->on('progress', function ($video, $format, $percentage) {
					echo "$percentage % transcoded";
					$_SESSION["newsession"] = $percentage;
					echo $this->percentageTrans;
					return;
				});
				try {
					$test = $video->save($format, $externalUrl[$i].$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);
					echo $test;
				} catch (ExecutionFailureException $th) {
					echo $th;
				}
				$good = true;
				/*if ($video = $ffmpeg->open($externalUrl[$i].$directory.'/'.$nameOfFile) === TRUE) {
					
				}*/
			}
		}else{
				$video = $ffmpeg->open($this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'/'.$nameOfFile);
				$format->on('progress', function ($video, $format, $percentage) {
					echo "$percentage % transcoded";
					$percentageTrans = $percentage;
				});
				try {
					$video->save($format, $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'//'.pathinfo($nameOfFile)['filename'].'.'.$type);
					echo $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.'//'.pathinfo($nameOfFile)['filename'].'.'.$type;
					self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.pathinfo($nameOfFile)['filename'].'.'.$type);					
				} catch (ExecutionFailureException $th) {
					echo $th;
				}
		}
	}




	public function extractHereRar($nameOfFile, $directory, $external) {
		if ($external){
			$good = false;
			$externalUrl = $this->config->getSystemValue('external', '');
			for ($i=0; $i < sizeof($externalUrl) && !$good && $externalUrl[$i] != null; $i++){
				if (file_exists($externalUrl[$i].$directory."/".$nameOfFile)){
					$good = true;
					if (extension_loaded ("rar")){
						$rar_file = rar_open($externalUrl[$i].$directory.'/'.$nameOfFile);
						$list = rar_list($rar_file);
						foreach($list as $file) {
							$entry = rar_entry_get($rar_file, $file->getName());
							$entry->extract($externalUrl[$i].$directory.'/'); 
						}
						rar_close($rar_file);
					}else{
						exec("unrar x '".$externalUrl[$i].$directory."/".$nameOfFile."' -R '".$externalUrl[$i].$directory."' -o+",$output,$return);
					}
				}
			}
		}else{
			$file = $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.$nameOfFile;
			$dir = $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory;
			if (extension_loaded ("rar")){
				$rar_file = rar_open($file);
				$list = rar_list($rar_file);
				foreach($list as $fileOpen) {
					echo "two";
					$entry = rar_entry_get($rar_file, $fileOpen->getName());
					$entry->extract($dir); // extract to the current dir
					self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.$fileOpen->getName());
				}
				rar_close($rar_file); 
			}else{
				exec("unrar x ".$file." -R ".$dir." -o+",$output,$return);
				foreach ($output as $val ) {
					if(preg_split('/ /', $val, -1, PREG_SPLIT_NO_EMPTY)[0] == "Extracting" && 
					preg_split('/ /', $val, -1, PREG_SPLIT_NO_EMPTY)[1] != "from"){
						$fichier = substr(strrchr($PATH, "/"), 1);
						self::scanFolder('/'.$this->UserId.'/files'.$directory.'/'.$fichier);
					}
				}
			}
		}
	}
	public function extractHereTar($nameOfFile, $directory, $external) {
		$file = $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory.$nameOfFile;
		$dir = $this->config->getSystemValue('datadirectory', '').'/'.$this->UserId.'/files'.$directory;
		/*$archive = new PharData($theTar);
		// error cheecking excluded    
		foreach($archive as $entry) {
		$extractDir = basename($file) . '/';
		if($file->isDir()) {
			$dir = new PharData($file->getPathname());          
			foreach($dir as $child) {
			$extract_file = $extractDir . basename($child);
			$archive->extractTo('/mypath/my-dir', $extract_file, true);
			}
		}
		}*/
		// decompress from gz
		
		if (strpos($nameOfFile, '.tar.')){
			$p = new PharData($file);
			$p->decompress();

			$phar = new PharData($dir."/".$p.".tar");
			//$phar->extractTo($dir); 
			foreach ($phar as $file) {
				echo basename($file);
				$phar->extractTo($dir, basename($file), true);
			}
			unlink($dir."/".$p.".tar"); 
		}else{

		}
			

		/*$p->decompress(); // creates files.tar
		
		echo $p;
		// unarchive from the tar
		$phar = new PharData("extract.tar");
		$phar->extractTo($dir); 
		/*try {
			$phar = new PharData('monphar.tar');
			$phar->extractTo('/chemin/complet'); // extrait tous les fichiers
			$phar->extractTo('/un/autre/chemin', 'fichier.txt'); // extrait seulement fichier.txt
			$phar->extractTo('/ce/chemin',
				array('fichier1.txt', 'fichier2.txt')); // extrait seulement 2 fichiers
			$phar->extractTo('/troisieme/chemin', null, true); // extrait tous les fichiers, en écrasant
		} catch (Exception $e) {
			// on traite les erreurs
		}
		if ($external){
		}else{

		} */

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