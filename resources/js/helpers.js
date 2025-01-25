import { parseISO, differenceInYears, format, differenceInDays } from 'date-fns';
import { ru } from 'date-fns/locale';
import axios from 'axios';

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
// toLocaleDateString('ru-RU', { month: 'long' });
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
        //console.error('Ошибка вычисления разницы в годах:', error);
        return null; // Возвращаем null в случае ошибки
    }
};

export const calculateDeadlineDate = (years, createDate) => {
    const date = new Date(createDate);
    // console.log('Дата ебать', date);

    // Сохраняем день и месяц из даты подписания
    const day = date.getDate();
    const month = date.getMonth();

    // Прибавляем годы
    date.setFullYear(date.getFullYear() + years);

    // Проверяем, чтобы месяц и день совпадали после изменения года
    // Если дата сместилась (например, 29 февраля в невисокосном году), мы устанавливаем исходный день
    if (date.getMonth() !== month) {
        date.setDate(0); // Устанавливаем последний день предыдущего месяца
    } else {
        date.setDate(day); // Восстанавливаем день
    }

    return date.toISOString().substr(0, 10); // Преобразуем в формат yyyy-mm-dd
};

export const fetchData = async (router, params = {}) => {
    try {
        // Проверка на наличие параметра "manager", если его нет, отправляем запрос без параметров
        //const url = params.user ? route(router, { user: params.user }) : route(router); // Если параметр "manager" не передан, просто вызываем URL без него
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
        // console.log(response);

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
