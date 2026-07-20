const operationSelect = document.getElementById('id_type_operation');

const multipleTransferOption = document.getElementById('multiple-transfer-option');
const multipleTransferInput = document.getElementById('envoi_multiple');

const beneficiaryGroup = document.getElementById('beneficiary-group');
const beneficiaryInput = document.getElementById('numero_beneficiaire');

const multipleBeneficiariesGroup = document.getElementById('multiple-beneficiaries-group');
const multipleBeneficiariesInput = document.getElementById('numeros_beneficiaires');

const withdrawalFeesGroup = document.getElementById('include-withdrawal-fees-group');
const withdrawalFeesInput = withdrawalFeesGroup.querySelector('input');
const operatorPrefix = withdrawalFeesGroup.dataset.operatorPrefix;

function normalizeNumber(number) {
    return number.replace(/\s+/g, '');
}

function getMultipleNumbers() {
    return multipleBeneficiariesInput.value
        .split(/[\r\n,;]+/)
        .map(normalizeNumber)
        .filter(Boolean);
}

function isTransferSelected() {
    const selectedOption = operationSelect.options[operationSelect.selectedIndex];

    return selectedOption.text.trim().toLowerCase() === 'transfert';
}

function updateOperationFields() {
    const isTransfer = isTransferSelected();
    const isMultiple = isTransfer && multipleTransferInput.checked;

    multipleTransferOption.hidden = !isTransfer;

    beneficiaryGroup.hidden = !isTransfer || isMultiple;
    beneficiaryInput.disabled = !isTransfer || isMultiple;
    beneficiaryInput.required = isTransfer && !isMultiple;

    multipleBeneficiariesGroup.hidden = !isMultiple;
    multipleBeneficiariesInput.disabled = !isMultiple;
    multipleBeneficiariesInput.required = isMultiple;

    if (!isTransfer) {
        multipleTransferInput.checked = false;
    }

    const beneficiaryNumbers = isMultiple
        ? getMultipleNumbers()
        : [normalizeNumber(beneficiaryInput.value)].filter(Boolean);

    const allNumbersUseSimulatedOperator = operatorPrefix !== ''
        && beneficiaryNumbers.length > 0
        && beneficiaryNumbers.every(number => number.startsWith(operatorPrefix));

    withdrawalFeesGroup.hidden = !isTransfer || !allNumbersUseSimulatedOperator;

    if (withdrawalFeesGroup.hidden) {
        withdrawalFeesInput.checked = false;
    }

    let validationMessage = '';

    if (isMultiple && beneficiaryNumbers.length === 1) {
        validationMessage = 'Saisissez au moins deux numéros.';
    } else if (isMultiple && beneficiaryNumbers.length > 20) {
        validationMessage = 'Vous pouvez saisir 20 numéros au maximum.';
    } else if (isMultiple && new Set(beneficiaryNumbers).size !== beneficiaryNumbers.length) {
        validationMessage = 'Un même numéro ne peut apparaître qu’une fois.';
    } else if (isMultiple && beneficiaryNumbers.length > 0
        && (operatorPrefix === ''
            || beneficiaryNumbers.some(number => !number.startsWith(operatorPrefix)))) {
        validationMessage = 'L’envoi multiple est réservé aux numéros du même opérateur.';
    }

    multipleBeneficiariesInput.setCustomValidity(validationMessage);
}

operationSelect.addEventListener('change', updateOperationFields);
multipleTransferInput.addEventListener('change', updateOperationFields);
beneficiaryInput.addEventListener('input', updateOperationFields);
multipleBeneficiariesInput.addEventListener('input', updateOperationFields);

updateOperationFields();
