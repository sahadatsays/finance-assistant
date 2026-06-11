import {
    Baby,
    Banknote,
    Briefcase,
    Building,
    Car,
    CircleDollarSign,
    Coffee,
    Dog,
    Dumbbell,
    Fuel,
    Gift,
    GraduationCap,
    HeartPulse,
    Home,
    Plane,
    Receipt,
    Shirt,
    ShoppingCart,
    Smartphone,
    Sparkles,
    Tag,
    Tv,
    Utensils,
    Wallet,
    type LucideIcon,
} from 'lucide-react';

export const CATEGORY_ICON_OPTIONS = [
    'tag',
    'banknote',
    'briefcase',
    'shopping-cart',
    'car',
    'home',
    'heart-pulse',
    'tv',
    'gift',
    'receipt',
    'building',
    'wallet',
    'utensils',
    'plane',
    'graduation-cap',
    'shirt',
    'fuel',
    'smartphone',
    'coffee',
    'dumbbell',
    'baby',
    'dog',
    'sparkles',
    'circle-dollar-sign',
] as const;

export type CategoryIconCode = (typeof CATEGORY_ICON_OPTIONS)[number];

const ICON_MAP: Record<CategoryIconCode, LucideIcon> = {
    tag: Tag,
    banknote: Banknote,
    'shopping-cart': ShoppingCart,
    briefcase: Briefcase,
    car: Car,
    home: Home,
    'heart-pulse': HeartPulse,
    tv: Tv,
    gift: Gift,
    receipt: Receipt,
    building: Building,
    wallet: Wallet,
    utensils: Utensils,
    plane: Plane,
    'graduation-cap': GraduationCap,
    shirt: Shirt,
    fuel: Fuel,
    smartphone: Smartphone,
    coffee: Coffee,
    dumbbell: Dumbbell,
    baby: Baby,
    dog: Dog,
    sparkles: Sparkles,
    'circle-dollar-sign': CircleDollarSign,
};

export function resolveCategoryIcon(
    code: string | null | undefined,
): LucideIcon {
    if (code && code in ICON_MAP) {
        return ICON_MAP[code as CategoryIconCode];
    }

    return Tag;
}
