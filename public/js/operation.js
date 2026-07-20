const operationSelect = document.getElementById('id_type_operation');
const beneficiaryGroup = document.getElementById('beneficiary-group');
const beneficiaryInput = document.getElementById('numero_beneficiaire');

function updateBeneficiaryField() {
    const selectedOption = operationSelect.options[operationSelect.selectedIndex];
    const isTransfer = selectedOption.text.trim().toLowerCase() === 'transfert';

    beneficiaryGroup.hidden = !isTransfer;
    beneficiaryInput.required = isTransfer;

    if (!isTransfer) {
        beneficiaryInput.value = '';
    }
}

operationSelect.addEventListener('change', updateBeneficiaryField);
updateBeneficiaryField();
