<?php

namespace go\modules\community\syncfusion\controller;

use go\core\Controller;
use go\core\fs\Blob;
use go\modules\community\syncfusion\model\Settings;
use Firebase\JWT\JWT;

/**
 * JMAP controller for Syncfusion file conversion.
 *
 * Converts files between native formats (DOCX, XLSX) and Syncfusion editor formats (SFDT, JSON)
 * using the Syncfusion Docker services.
 */
class Editor extends Controller
{
	/**
	 * Document extensions the client-side editor cannot export back to their
	 * original format (it can only produce DOCX / TXT / SFDT). Saving one of
	 * these creates a new sibling .docx file instead of replacing the original.
	 *
	 * @var string[]
	 */
	private const CONVERT_TO_DOCX = ['doc', 'dotx', 'rtf'];

	/**
	 * Spreadsheet save types supported by the Syncfusion save service,
	 * with the file extension and MIME type for the resulting blob.
	 *
	 * @var array<string, array{ext: string, mime: string}>
	 */
	private const SPREADSHEET_SAVE_TYPES = [
		'Xlsx' => ['ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
		'Xls'  => ['ext' => 'xls',  'mime' => 'application/vnd.ms-excel'],
		'Csv'  => ['ext' => 'csv',  'mime' => 'text/csv'],
	];

	/**
	 * Convert a document file to SFDT format for the DocumentEditorContainer.
	 *
	 * Accepts either a fileId (legacy Files module) or a blobId (JMAP blob).
	 *
	 * @param array $params {fileId?: int, blobId?: string}
	 * @return array The SFDT data from the Docker service
	 */
	public function openDocument(array $params): array
	{
		$settings = Settings::get();
		if (empty($settings->documentServiceUrl)) {
			return ['success' => false, 'error' => 'Document service URL is not configured.'];
		}

		$fileContent = $this->getFileContent($params);
		if ($fileContent === null) {
			return ['success' => false, 'error' => 'File not found or access denied.'];
		}

		$serviceUrl = rtrim($settings->documentServiceUrl, '/') . '/Import';

		$result = $this->sendToService($serviceUrl, $fileContent['content'], $fileContent['name']);
		if ($result === null) {
			return ['success' => false, 'error' => 'Failed to convert document via Docker service.'];
		}

		return [
			'success' => true,
			'sfdt' => $result,
			'fileName' => $fileContent['name'],
			'canEdit' => $fileContent['canEdit'],
		];
	}

	/**
	 * Convert a spreadsheet file to JSON format for the Spreadsheet component.
	 *
	 * @param array $params {fileId?: int, blobId?: string}
	 * @return array The spreadsheet JSON data
	 */
	public function openSpreadsheet(array $params): array
	{
		$settings = Settings::get();
		if (empty($settings->spreadsheetServiceUrl)) {
			return ['success' => false, 'error' => 'Spreadsheet service URL is not configured.'];
		}

		$fileContent = $this->getFileContent($params);
		if ($fileContent === null) {
			return ['success' => false, 'error' => 'File not found or access denied.'];
		}

		$serviceUrl = rtrim($settings->spreadsheetServiceUrl, '/') . '/Open';

		$result = $this->sendToService($serviceUrl, $fileContent['content'], $fileContent['name']);
		if ($result === null) {
			return ['success' => false, 'error' => 'Failed to convert spreadsheet via Docker service.'];
		}

		return [
			'success' => true,
			'data' => $result,
			'fileName' => $fileContent['name'],
			'canEdit' => $fileContent['canEdit'],
		];
	}

	/**
	 * Save an edited document back to a file or blob.
	 *
	 * The frontend uploads the edited file as a blob first, then calls this method
	 * to replace the original file content.
	 *
	 * @param array $params {blobId: string, fileId?: int}
	 * @return array
	 */
	public function save(array $params): array
	{
		if (empty($params['blobId'])) {
			return ['success' => false, 'error' => 'blobId is required.'];
		}

		if (!empty($params['fileId'])) {
			return $this->saveToFile((int)$params['fileId'], $params['blobId']);
		}

		// Blob-only mode: nothing to do, the blob is already uploaded
		return ['success' => true, 'blobId' => $params['blobId']];
	}

	/**
	 * Convert spreadsheet JSON data back to XLSX (or other format) via the Docker service.
	 *
	 * The Syncfusion Spreadsheet component saves data as JSON via saveAsJson().
	 * This method sends that JSON to the spreadsheet service /Save endpoint to convert
	 * it back to a proper file format (XLSX, CSV, etc.) and returns a blob.
	 *
	 * @param array $params {jsonData: string, fileName: string, saveType?: string}
	 * @return array {success: bool, blobId?: string, error?: string}
	 */
	public function exportSpreadsheet(array $params): array
	{
		$settings = Settings::get();
		if (empty($settings->spreadsheetServiceUrl)) {
			return ['success' => false, 'error' => 'Spreadsheet service URL is not configured.'];
		}

		$jsonData = $params['jsonData'] ?? '';
		$fileName = $params['fileName'] ?? 'spreadsheet.xlsx';
		$saveType = $params['saveType'] ?? 'Xlsx';

		if (!isset(self::SPREADSHEET_SAVE_TYPES[$saveType])) {
			$saveType = 'Xlsx';
		}

		if (empty($jsonData)) {
			return ['success' => false, 'error' => 'No data to save.'];
		}

		$serviceUrl = rtrim($settings->spreadsheetServiceUrl, '/') . '/Save';

		$boundary = '----GoSyncfusion' . uniqid();

		$fields = [
			'FileName' => $fileName,
			'saveType' => $saveType,
			'JSONData' => $jsonData,
			'PdfLayoutSettings' => json_encode(['FitSheetOnOnePage' => false]),
		];

		$body = '';
		foreach ($fields as $name => $value) {
			$body .= "--{$boundary}\r\n"
				. "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n"
				. $value . "\r\n";
		}
		$body .= "--{$boundary}--\r\n";

		$headers = [
			'Content-Type: multipart/form-data; boundary=' . $boundary,
			'Content-Length: ' . strlen($body),
		];

		$secret = $settings->serviceSecret;
		if (!empty($secret)) {
			$payload = [
				'iat' => time(),
				'exp' => time() + 300,
			];
			$token = JWT::encode($payload, $secret, 'HS256');
			$headers[] = 'Authorization: Bearer ' . $token;
			$headers[] = 'X-Service-Token: ' . $secret;
		}

		$ch = curl_init($serviceUrl);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_CONNECTTIMEOUT => 10,
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		if (!is_string($response) || $httpCode !== 200) {
			go()->debug("Syncfusion service error: HTTP {$httpCode}, curl error: {$error}");
			return ['success' => false, 'error' => 'Failed to export spreadsheet via Docker service.'];
		}

		$tmpFile = \go\core\fs\File::tempFile(self::SPREADSHEET_SAVE_TYPES[$saveType]['ext']);
		file_put_contents($tmpFile->getPath(), $response);

		$blob = Blob::fromTmp($tmpFile);
		$blob->name = $fileName;
		$blob->type = self::SPREADSHEET_SAVE_TYPES[$saveType]['mime'];
		$blob->save();

		return [
			'success' => true,
			'blobId' => $blob->id,
		];
	}

	/**
	 * Read file content from either a legacy file or a blob.
	 *
	 * @param array $params
	 * @return array{content: string, name: string, canEdit: bool}|null
	 */
	private function getFileContent(array $params): ?array
	{
		if (!empty($params['fileId'])) {
			return $this->getFileContentFromFileId((int)$params['fileId']);
		}

		if (!empty($params['blobId'])) {
			return $this->getFileContentFromBlobId($params['blobId']);
		}

		return null;
	}

	/**
	 * @param int $fileId
	 * @return array{content: string, name: string, canEdit: bool}|null
	 */
	private function getFileContentFromFileId(int $fileId): ?array
	{
		$fileRecord = \GO\Files\Model\File::model()->findByPk($fileId);
		if (!$fileRecord) {
			return null;
		}

		if ($fileRecord->getPermissionLevel() < \GO\Base\Model\Acl::READ_PERMISSION) {
			return null;
		}

		$fsFile = $fileRecord->fsFile;
		if (!$fsFile->exists()) {
			return null;
		}

		$canEdit = $fileRecord->getPermissionLevel() >= \GO\Base\Model\Acl::WRITE_PERMISSION
			&& !$fileRecord->isLocked();

		return [
			'content' => $fsFile->getContents(),
			'name' => $fileRecord->name,
			'canEdit' => $canEdit,
		];
	}

	/**
	 * @param string $blobId
	 * @return array{content: string, name: string, canEdit: bool}|null
	 */
	private function getFileContentFromBlobId(string $blobId): ?array
	{
		$blob = Blob::findById($blobId);
		if (!$blob) {
			return null;
		}

		$file = $blob->getFile();
		if (!$file || !$file->exists()) {
			return null;
		}

		$content = $file->getContents();
		if ($content === false) {
			return null;
		}

		return [
			'content' => $content,
			'name' => $blob->name,
			'canEdit' => true,
		];
	}

	/**
	 * Send file content to a Syncfusion Docker service endpoint for conversion.
	 *
	 * @param string $serviceUrl
	 * @param string $fileContent Binary file content
	 * @param string $fileName
	 * @return mixed Decoded JSON response, or null on failure
	 */
	private function sendToService(string $serviceUrl, string $fileContent, string $fileName)
	{
		$boundary = '----GoSyncfusion' . uniqid();

		// Strip characters that would break out of the multipart header
		$safeName = preg_replace('/[\r\n"\\\\]/', '', $fileName);

		$body = "--{$boundary}\r\n"
			. "Content-Disposition: form-data; name=\"files\"; filename=\"" . $safeName . "\"\r\n"
			. "Content-Type: application/octet-stream\r\n\r\n"
			. $fileContent . "\r\n"
			. "--{$boundary}--\r\n";

		$headers = [
			'Content-Type: multipart/form-data; boundary=' . $boundary,
			'Content-Length: ' . strlen($body),
		];

		$secret = Settings::get()->serviceSecret;
		if (!empty($secret)) {
			$payload = [
				'iat' => time(),
				'exp' => time() + 300,
			];
			$token = JWT::encode($payload, $secret, 'HS256');
			$headers[] = 'Authorization: Bearer ' . $token;
			// Static token for simple nginx proxy validation
			$headers[] = 'X-Service-Token: ' . $secret;
		}

		$ch = curl_init($serviceUrl);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_CONNECTTIMEOUT => 10,
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		if (!is_string($response) || $httpCode !== 200) {
			go()->debug("Syncfusion service error: HTTP {$httpCode}, curl error: {$error}");
			return null;
		}

		$decoded = json_decode($response, true);
		return $decoded !== null ? $decoded : $response;
	}

	/**
	 * Save a blob back to the Files module.
	 *
	 * Formats the editor can round-trip replace the original file's content.
	 * Formats it cannot export (see CONVERT_TO_DOCX) are saved as a new
	 * sibling .docx file; the original file stays untouched.
	 *
	 * @param int $fileId
	 * @param string $blobId
	 * @return array
	 */
	private function saveToFile(int $fileId, string $blobId): array
	{
		$fileRecord = \GO\Files\Model\File::model()->findByPk($fileId);
		if (!$fileRecord) {
			return ['success' => false, 'error' => 'File not found.'];
		}

		$blob = Blob::findById($blobId);
		if (!$blob) {
			return ['success' => false, 'error' => 'Blob not found.'];
		}

		if (in_array(strtolower($fileRecord->extension), self::CONVERT_TO_DOCX, true)) {
			return $this->saveAsNewDocx($fileRecord, $blob);
		}

		if ($fileRecord->getPermissionLevel() < \GO\Base\Model\Acl::WRITE_PERMISSION) {
			return ['success' => false, 'error' => 'Access denied.'];
		}

		if ($fileRecord->isLocked()) {
			return ['success' => false, 'error' => 'File is locked.'];
		}

		$tmpFile = \GO\Base\Fs\File::tempFile('', $fileRecord->extension);
		file_put_contents($tmpFile->path(), $blob->getFile()->getContents());

		if (!$fileRecord->replace($tmpFile)) {
			return ['success' => false, 'error' => 'Failed to replace file content.'];
		}

		return ['success' => true];
	}

	/**
	 * Create a new .docx file next to the original for formats the editor
	 * cannot export back (doc, dotx, rtf).
	 *
	 * @param \GO\Files\Model\File $fileRecord The original file
	 * @param \go\core\fs\Blob $blob Blob holding the exported DOCX content
	 * @return array
	 */
	private function saveAsNewDocx($fileRecord, Blob $blob): array
	{
		$folder = $fileRecord->folder;
		if (!$folder || $folder->getPermissionLevel() < \GO\Base\Model\Acl::WRITE_PERMISSION) {
			return ['success' => false, 'error' => 'Access denied.'];
		}

		$baseName = \GO\Base\Fs\File::stripInvalidChars($fileRecord->fsFile->nameWithoutExtension());
		$newName = $baseName . '.docx';

		$tmpFile = \GO\Base\Fs\File::tempFile('', 'docx');
		file_put_contents($tmpFile->path(), $blob->getFile()->getContents());

		$newFile = $folder->addFilesystemFile($tmpFile, true, $newName);
		if (!$newFile) {
			return ['success' => false, 'error' => 'Failed to create the converted file.'];
		}

		return [
			'success' => true,
			'newFileId' => (int) $newFile->id,
			'newFileName' => $newFile->name,
		];
	}
}
