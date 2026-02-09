<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WriterController extends Controller
{
    public function makeApiCall(Request $request)
    {
        // Prepare the data for the POST request
        $postData = [
            'model' => 'palmyra-x-v2',
            'prompt' => 'Provide a sumary of Accenture specifically include their industry groups.',
            'max_tokens' => '250',
            'temperature' => .2,
            'stream' => false,
        ];

        // Make the POST request to the external API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer N4aAnyzVK0cjnEFv9CG7u4RQaCg9J0JI', 
        ])->post('https://api.writer.com/v1/completions', $postData);

        // Check if the request was successful
        if ($response->successful()) {
            // API call was successful
            $responseData = $response->json('choices.0.text');
            // Process the response data as needed
        } else {
            // API call failed
            $statusCode = $response->status(); // Get the HTTP status code
            $errorMessage = $response->body(); // Get the error message
            // Handle the error
        }

        // Decode the JSON response
//        $responseData = json_decode($responseData, true); // Convert JSON string to associative array
        
        // Extract the text from the JSON response
        $text = $responseData; // Replace this with the actual path to the text in your JSON response

        $html = Str::replace("\n", '<br>', $text);
        $count = substr_count($text, '**');
        $replaceStrong = true;
        
        for ($i = 0; $i < $count; $i++) {
            $needle = $replaceStrong ? '**' : '**';
            $replace = $replaceStrong ? '<strong>' : '</strong>';
            $html = $replaceStrong ? Str::replaceFirst($needle, $replace, $html) : Str::replaceLast($needle, $replace, $html);
            $replaceStrong = !$replaceStrong;
        }
        
        // Return the formatted HTML
        return $html;

    }}
