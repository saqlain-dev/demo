<?php

namespace App\Enum;

use App\Traits\EnumToArray;

enum InventoryType: int {

    use EnumToArray;
    case FixedAsset = 1;
    case ConsumableItem = 2;
    case PrintingMaterial = 3;
    case GeneralInventory = 4;

}


