<?php

namespace WorldOS\Domains\Cosmology;

enum Archetype: string
{
    case ASCENSION_MYSTICISM = 'ascension_mysticism';
    case TECH_STRATIFIED = 'tech_stratified';
    case PRIMAL_SURVIVAL = 'primal_survival';
    case COSMIC_HORROR = 'cosmic_horror';
    case HIGH_FANTASY = 'high_fantasy';
    case POST_APOCALYPTIC = 'post_apocalyptic';
    case UTOPIAN_DREAM = 'utopian_dream';
    case GRIMDARK_WARFARE = 'grimdark_warfare';

    public function label(): string
    {
        return match($this) {
            self::ASCENSION_MYSTICISM => 'Tu Chân / Huyền Huyễn',
            self::TECH_STRATIFIED => 'Cyberpunk / Khoa Huyễn',
            self::PRIMAL_SURVIVAL => 'Man Hoang / Sinh Tồn',
            self::COSMIC_HORROR => 'Kinh Dị Vũ Trụ',
            self::HIGH_FANTASY => 'Kỳ Ảo Phương Tây',
            self::POST_APOCALYPTIC => 'Hậu Tận Thế',
            self::UTOPIAN_DREAM => 'Không Tưởng / Hòa Bình',
            self::GRIMDARK_WARFARE => 'Chiến Tranh Tàn Khốc',
        };
    }
}
