const operationSelect = document.getElementById('id_type_operation');
const beneficiaryGroup = document.getElementById('beneficiary-group');
const beneficiaryInput = document.getElementById('numero_beneficiaire');
const withdrawalFeesGroup = document.getElementById('include-withdrawal-fees-group');
const withdrawalFeesInput = withdrawalFeesGroup.querySelector('input');

function updateOperationFields() {
    const selectedOption = operationSelect.options[operationSelect.selectedIndex];
    const isTransfer = selectedOption.text.trim().toLowerCase() === 'transfert';
    const beneficiaryNumber = beneficiaryInput.value.replace(/\s+/g, '');
    const operatorPrefix = withdrawalFeesGroup.dataset.operatorPrefix;
    const isSameOperator = operatorPrefix !== ''
        && beneficiaryNumber.startsWith(operatorPrefix);

    beneficiaryGroup.hidden = !isTransfer;
    beneficiaryInput.required = isTransfer;
    withdrawalFeesGroup.hidden = !isTransfer || !isSameOperator;

    if (!isTransfer) {
        beneficiaryInput.value = '';
    }

    if (withdrawalFeesGroup.hidden) {
        withdrawalFeesInput.checked = false;
    }
}

operationSelect.addEventListener('change', updateOperationFields);
beneficiaryInput.addEventListener('input', updateOperationFields);
updateOperationFields();
