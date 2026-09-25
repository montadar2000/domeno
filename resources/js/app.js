const MAX_POINTS = 200;

const pointsInput = document.querySelector('#points');

if (pointsInput instanceof HTMLInputElement) {
    const englishDigits = (value) => value
        .replace(/[\u0660-\u0669]/g, (digit) => String(digit.charCodeAt(0) - 0x0660))
        .replace(/[\u06f0-\u06f9]/g, (digit) => String(digit.charCodeAt(0) - 0x06f0))
        .replace(/\D/g, '');

    const read = () => {
        const digits = englishDigits(pointsInput.value).replace(/^0+(?=\d)/, '');

        if (digits === '') {
            return null;
        }

        return Math.min(MAX_POINTS, Number(digits));
    };

    const write = (value) => {
        pointsInput.value = value === null ? '' : String(value);
    };

    pointsInput.addEventListener('input', () => {
        write(read());
    });

    document.querySelector('#points-plus')?.addEventListener('click', () => {
        write(Math.min(MAX_POINTS, (read() ?? 0) + 1));
        pointsInput.focus();
    });

    document.querySelector('#points-minus')?.addEventListener('click', () => {
        const current = read();

        if (current === null || current <= 1) {
            write(current === null ? null : 1);
            pointsInput.focus();

            return;
        }

        write(current - 1);
        pointsInput.focus();
    });
}
