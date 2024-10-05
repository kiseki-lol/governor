<?php

/*
abstract class AssetType
{
    public const TSHIRT = 'tshirt';
    public const SHIRT = 'shirt';
    public const PANTS = 'pants';
    public const HAT = 'hat';
    public const FACE = 'face';
    public const HEAD = 'head';
}*/

enum AssetType : string
{
    case TSHIRT = 'tshirt';
    case SHIRT = 'shirt';
    case PANTS = 'pants';
    case HAT = 'hat';
    case FACE = 'face';
    case HEAD = 'head';
}