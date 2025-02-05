import { parseISO, differenceInYears, format } from 'date-fns';
import { ru } from 'date-fns/locale';
import axios from 'axios';

export const formatDate = (date) => {
    try {
        return format(parseISO(date), 'd MMMM yyyy', { locale: ru });
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};
export const formatDateLogs = (date) => {
    try {
        return format(parseISO(date), 'dd/MM/yyyy, HH:mm:ss');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};
export const formatDateClientContract = (date) => {
    try {
        return format(parseISO(date), 'dd/MM/yyyy');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};

export const formatDateClientContractRus = (date) => {
    try {
        const parsedDate = parseISO(date);

        const formattedDate = format(parsedDate, 'MMMM yyyy', { locale: ru });

        return formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};
export const formatDateNotificztion = (date) => {
    try {
        return format(parseISO(date), 'dd.MM.yyyy');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};
export const formatTimeNotificztion = (date) => {
    try {
        return format(parseISO(date), 'HH:mm');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};

export const getYearDifference = (startDate, endDate) => {
    try {
        return differenceInYears(parseISO(endDate), parseISO(startDate));
    } catch (error) {
        return null;
    }
};

export const calculateDeadlineDate = (years, createDate) => {
    const date = new Date(createDate);

    const day = date.getDate();
    const month = date.getMonth();

    date.setFullYear(date.getFullYear() + years);

    if (date.getMonth() !== month) {
        date.setDate(0);
    } else {
        date.setDate(day);
    }

    return date.toISOString().substr(0, 10);
};

export const fetchData = async (router, params = {}) => {
    try {
        const url = params.user
            ? route(router, { user: params.user })
            : params.contract
            ? route(router, { contract: params.contract })
            : params.client
            ? route(router, { client: params.client })
            : params.application
            ? route(router, { application: params.application })
            : route(router);
        const response = await axios.get(url);

        return response.data;
    } catch (error) {
        console.error('Ошибка при выполнении GET запроса:', error);
        throw error;
    }
};
export const formatNumber = (num) => {
    try {
        return Number(num)
            .toLocaleString('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
            .replace(',', '.');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return num;
    }
};

export const formatNumberBalanceTransactions = (num) => {
    try {
        return Number(num)
            .toLocaleString('ru-RU', {
                maximumFractionDigits: 0,
            })
            .replace(',', '.');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return num;
    }
};

export const formatDateBalanceTransactions = (date) => {
    try {
        return format(parseISO(date), 'LLLL yyyy', { locale: ru }).replace(/^./, (str) => str.toUpperCase());
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date;
    }
};
