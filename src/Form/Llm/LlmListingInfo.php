<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Llm;

/**
 * Additional informations and behaviors around a LLM-generated listing
 */
interface LlmListingInfo
{

    static public function getEntryDumpJs(): string;

}
