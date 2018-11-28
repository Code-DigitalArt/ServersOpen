<?php
require('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(1);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

function go($output) {
	if (!$handle = fopen('redirects.csv', 'r')) {
		echo 'Could not open file'; exit;
	}

	$newHandle   = fopen('newRedirects.csv', 'w');
	$noSkuHandle = fopen('noSku.csv', 'w');
	$nginxHandle = fopen('nginx.txt', 'w');

	fputcsv($newHandle, fgetcsv($handle, 5000, ','));

	$search = '/ProductDetails.asp?ProductCode=';

	$count = 0;
	while (($row = fgetcsv($handle, 5000, ',')) !== false) {
		echo $output ? '#' . ++$count . ' - memory - ' . (memory_get_usage() / 1000000) . PHP_EOL: '';

		$product = Mage::getModel('catalog/product');
		$product->load($product->getIdBySku($row[1]));

		if ($product->getUrlKey() && $product->getUrlKey() != '') {
			$url = '/' . $product->getUrlKey();
		} else {
			$url = '/';
			fputcsv($noSkuHandle, [$row[1]]);
		}

		$nginx = 'if ($arg_ProductCode = ' . str_replace($search, '', $row[0]) . ') {' . PHP_EOL
				. '    return 301 ' . $url . ';' . PHP_EOL
				. '}' . PHP_EOL;
		$row[2] = $url;
		fputcsv($newHandle, $row);
		fwrite($nginxHandle, $nginx);
	}

	fclose($handle);
	fclose($newHandle);
	fclose($noSkuHandle);
	fclose($nginxHandle);
}

go(true);