import { parseISO, differenceInYears, format } from 'date-fns';
import { ru } from 'date-fns/locale';
import axios from 'axios';
import { route } from "ziggy-js";
import { Ziggy } from "@/ziggy.js";

export const formatDate = (date) => {
    try {
        return format(parseISO(date), 'd MMMM yyyy', { locale: ru });
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};
export const formatDateLogs = (date) => {
    try {
        return format(parseISO(date), 'dd/MM/yyyy, HH:mm:ss');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};
export const formatDateClientContract = (date) => {
    try {
        return format(parseISO(date), 'dd/MM/yyyy');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};

export const formatDateClientContractRus = (date) => {
    try {
        // Преобразуем строку в дату ISO
        const parsedDate = parseISO(date);

        // Форматируем дату как "декабрь 2024"
        const formattedDate = format(parsedDate, 'MMMM yyyy', { locale: ru });

        // Преобразуем первую букву в верхний регистр
        return formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};
export const formatDateNotificztion = (date) => {
    try {
        return format(parseISO(date), 'dd.MM.yyyy');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};
export const formatTimeNotificztion = (date) => {
    try {
        return format(parseISO(date), 'HH:mm');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};

export const getYearDifference = (startDate, endDate) => {
    try {
        return differenceInYears(parseISO(endDate), parseISO(startDate));
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return null; // Возвращаем null в случае ошибки
    }
};

export const calculateDeadlineDate = (years, createDate) => {
    const date = new Date(createDate);
    // Сохраняем день и месяц из даты подписания
    const day = date.getDate();
    const month = date.getMonth();

    // Прибавляем годы
    date.setFullYear(date.getFullYear() + years);

    if (date.getMonth() !== month && month === 1 && day === 29) {
        date.setMonth(2);  // Устанавливаем март
        date.setDate(1);   // Устанавливаем 1 марта
    } else {
        date.setDate(day); // Иначе возвращаем исходный день
    }

    return date.toISOString().substr(0, 10); // Преобразуем в формат yyyy-mm-dd
};



export const fetchData = async (router, params = {}) => {
    try {
        // Проверка на наличие параметра "manager", если его нет, отправляем запрос без параметров
        const url = params.user
            ? route(router, { user: params.user }, false, Ziggy)
            : params.contract
            ? route(router, { contract: params.contract }, false, Ziggy)
            : params.client
            ? route(router, { client: params.client }, false, Ziggy)
            : params.application
            ? route(router, { application: params.application }, false, Ziggy)
            : route(router, {}, false, Ziggy);
        const response = await axios.get(url);

        return response.data; // Возвращаем данные
    } catch (error) {
        console.error('Ошибка при выполнении GET запроса:', error);
        throw error; // Бросаем ошибку для обработки в другом месте
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
        return num; // Возвращаем исходное значение в случае ошибки
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
        return num; // Возвращаем исходное значение в случае ошибки
    }
};

export const formatDateBalanceTransactions = (date) => {
    try {
        return format(parseISO(date), 'LLLL yyyy', { locale: ru }).replace(/^./, (str) => str.toUpperCase());
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};

export const filterNegativeNumbers = (event) => {
    if(event.data === '-'){
        event.target.value = 1;
    }
}

