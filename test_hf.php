<?php
$models = [
    'Qwen/Qwen2.5-7B-Instruct',
    'qwen/qwen2.5-7b-instruct',
    'meta-llama/Llama-3.1-8B-Instruct',
    'meta-llama/Meta-Llama-3.1-8B-Instruct',
    'mistralai/Mistral-7B-Instruct-v0.3',
];

foreach ($models as $model) {
    $ch = curl_init('https://router.huggingface.co/novita/v3/openai/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer hf_your_token_here',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => 'Say hello']],
        'max_tokens' => 20
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo $model . ' -> ' . $status . PHP_EOL;
}
