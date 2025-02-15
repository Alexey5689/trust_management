import { Ziggy as ZiggyBase } from '../../vendor/tightenco/ziggy';

export const Ziggy = {
    ...ZiggyBase,
    url: import.meta.env.VITE_URL || 'https://api-tm.lb.pro-technologii.ru',
};