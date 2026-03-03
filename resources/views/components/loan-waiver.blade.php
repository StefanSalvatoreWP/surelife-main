{{--
    Loan Waiver of Rights Component
    Usage: @include('components.loan-waiver', ['client' => $client, 'contract' => $contract, 'loanRequestId' => $id])
--}}
<div id="waiverModal" class="modal fade" tabindex="-1" aria-labelledby="waiverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="waiverModalLabel">Waiver of Rights</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="waiver-content p-3 border rounded bg-light">
                    <h6 class="text-center mb-4">SURE LIFE CARE & SERVICES</h6>
                    <h5 class="text-center mb-4 text-decoration-underline">WAIVER OF RIGHTS</h5>

                    <p class="mb-3">
                        I <strong class="client-name-display">{{ $client->firstname ?? '' }} {{ $client->lastname ?? '' }}</strong>
                        member of Sure Life Care & Services with Contract Number
                        <strong class="contract-number-display">{{ $contract->contractnumber ?? ($client->contractnumber ?? '___________') }}</strong>
                        applied for a loan in my Contract.
                    </p>

                    <p class="mb-3">
                        I understand that after applying for a loan, I waive my right of any benefits and privileges
                        stated in the Contract as a member. In Case of loss of life, I also agreed that I have to pay
                        the remaining balance of my loan to be rendered service.
                    </p>

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <p class="mb-1">Applicant's Full name & signature:</p>
                            <div class="border-bottom py-2">
                                <strong>{{ $client->firstname ?? '' }} {{ $client->lastname ?? '' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1">Date:</p>
                            <div class="border-bottom py-2">
                                <strong>{{ date('F d, Y') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h6>Digital Signature</h6>
                <p class="text-muted small">Please sign below using your mouse or touch screen:</p>

                <div class="signature-container border rounded bg-white" style="position: relative;">
                    <canvas id="signatureCanvas" width="600" height="150" class="w-100"></canvas>
                    <button type="button" id="clearSignature" class="btn btn-sm btn-secondary position-absolute" style="top: 10px; right: 10px;">
                        <i class="fas fa-eraser"></i> Clear
                    </button>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="agreeWaiver">
                    <label class="form-check-label" for="agreeWaiver">
                        I have read and agree to the Waiver of Rights stated above
                    </label>
                </div>

                <input type="hidden" id="waiverClientName" value="{{ $client->firstname ?? '' }} {{ $client->lastname ?? '' }}">
                <input type="hidden" id="waiverContractNumber" value="{{ $contract->contractnumber ?? ($client->contractnumber ?? '') }}">
                <input type="hidden" id="waiverLoanRequestId" value="{{ $loanRequestId ?? '' }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="submitWaiver" class="btn btn-primary" disabled>
                    <i class="fas fa-check"></i> Sign & Submit
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let hasSignature = false;

    // Set up canvas
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDrawing(e) {
        e.preventDefault();
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        hasSignature = true;
        checkSubmitButton();
    }

    function stopDrawing() {
        isDrawing = false;
    }

    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    // Touch events
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    // Clear button
    document.getElementById('clearSignature').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
        checkSubmitButton();
    });

    // Agreement checkbox
    document.getElementById('agreeWaiver').addEventListener('change', checkSubmitButton);

    function checkSubmitButton() {
        const agreed = document.getElementById('agreeWaiver').checked;
        document.getElementById('submitWaiver').disabled = !(hasSignature && agreed);
    }

    // Submit waiver
    document.getElementById('submitWaiver').addEventListener('click', function() {
        const signatureData = canvas.toDataURL('image/png');
        const loanRequestId = document.getElementById('waiverLoanRequestId').value;
        const clientName = document.getElementById('waiverClientName').value;
        const contractNumber = document.getElementById('waiverContractNumber').value;

        fetch('{{ route("loan.waiver.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                loan_request_id: loanRequestId,
                client_name: clientName,
                contract_number: contractNumber,
                signature_data: signatureData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Waiver signed successfully!');
                $('#waiverModal').modal('hide');
                // Update UI to show waiver signed
                document.getElementById('waiverStatus').textContent = 'Signed';
                document.getElementById('waiverStatus').className = 'badge bg-success';
            } else {
                alert('Error saving waiver. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving waiver. Please try again.');
        });
    });
})();
</script>
@endpush
