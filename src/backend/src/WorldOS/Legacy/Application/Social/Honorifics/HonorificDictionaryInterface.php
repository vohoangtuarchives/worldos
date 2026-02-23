<?php

namespace WorldOS\Legacy\Application\Social\Honorifics;

interface HonorificDictionaryInterface
{
    /** @return string[] */
    public function selfReferences(): array;

    /** @return string[] */
    public function addressingOthers(): array;

    /** @return string[] */
    public function socialTitles(): array;
}
