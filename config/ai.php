<?php

$localConfig = __DIR__ . "/ai.local.php";

if (is_file($localConfig)) {
	require $localConfig;
}

$apiKey = $apiKey ?? getenv("OPENAI_API_KEY") ?: "";
$aiModel = $aiModel ?? getenv("OPENAI_MODEL") ?: "gpt-4o-mini";

?>