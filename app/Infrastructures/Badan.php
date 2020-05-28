<?php

namespace App\Infrastructures;

interface Badan
{
    public function getNamaAttribute(): string;
    public function getAlamatFormattedAttribute(): string;
    public function getKontakAttribute(): array;
    public function getPemimpinAttribute(): string;
    public function getKategoriAttribute(): string;
    public function getSummarizedAttribute(): array;
    public function getBidangsAttribute(): array;
    public function getPemegangsAttribute(): array;
}
