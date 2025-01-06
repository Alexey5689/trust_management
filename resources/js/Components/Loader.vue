<template>
    <div class="loader-overlay flex justify-center align-center">
        <div class="loader-body flex flex-column align-center justify-center r-gap">
            <div class="loader flex justify-center align-center">
                <div class="centre"></div>
            </div>
            <p>Идет загрузка...</p>
        </div>
    </div>
</template>

<style scoped>
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #24242429;
    z-index: 1000;
}

.loader-body {
    width: 204px;
    height: 117px;
    background: #fff;
    border-radius: 32px;
}

.loader {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: conic-gradient(from 90deg at 50% 50%,
            rgba(48, 133, 97, 0) 0deg,
            rgba(78, 159, 125, 0) 0.04deg,
            #4E9F7D 360deg);
    mask: radial-gradient(circle, rgba(0, 0, 0, 1) 60%, rgba(0, 0, 0, 0) 61%);
    animation: spin 1s linear infinite;
}

.centre {
    height: 22px;
    width: 22px;
    background: #fff;
    border-radius: 50%;
}

p {
    font-size: 20px;
    line-height: 29px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>