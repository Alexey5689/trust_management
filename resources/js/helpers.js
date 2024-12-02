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
        return format(parseISO(date), 'dd/MM/yyyy HH:mm:ss');
    } catch (error) {
        console.error('Ошибка форматирования даты:', error);
        return date; // Возвращаем исходное значение в случае ошибки
    }
};

export const getYearDifference = (startDate, endDate) => {
    try {
        return differenceInYears(parseISO(endDate), parseISO(startDate));
    } catch (error) {
        console.error('Ошибка вычисления разницы в годах:', error);
        return null; // Возвращаем null в случае ошибки
    }
};

export const getDayDifference = (startDate, endDate) => {
    try {
        return differenceInDays(parseISO(endDate), parseISO(startDate));
    } catch (error) {
        console.error('Ошибка вычисления разницы в днях:', error);
        return null;
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

// export const fetchData = async (url) => {
//     try {
//         const response = await axios.get(url);
//         return response.data; // Возвращаем данные
//     } catch (error) {
//         console.error('Ошибка при выполнении GET запроса:', error);
//         throw error; // Бросаем ошибку для обработки в другом месте
//     }
// };
export const fetchData = async (router, params = {}) => {
    try {
        // Проверка на наличие параметра "manager", если его нет, отправляем запрос без параметров
        const url = params.user ? route(router, { user: params.user }) : route(router); // Если параметр "manager" не передан, просто вызываем URL без него

        const response = await axios.get(url);
        return response.data; // Возвращаем данные
    } catch (error) {
        console.error('Ошибка при выполнении GET запроса:', error);
        throw error; // Бросаем ошибку для обработки в другом месте
    }
};
