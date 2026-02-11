<?php

namespace App\StoryEngine;

class CharacterState
{
    public int $powerTier = 0; // 0: Luyện Khí, 1: Trúc Cơ, etc.
    public int $exposure = 0;
    public int $chaptersInCurrentTier = 0;
}
