<div class="modal-body" style="max-height:700px; overflow: auto">
    <form>
        <div id="result-content-manna">
            <h5 class="tt-result">Manna</h5>
            <table class="table table-bordered">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Carrier Name</th>
                    <th scope="col">Shipping Method</th>
                    <th scope="col">Service</th>
                    <th scope="col">Transit Days</th>
                    <th scope="col">Est.Delivery</th>
                    <th scope="col">Quote</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="result-content-convey">
            <h5 class="tt-result">Convey</h5>
            <table class="table table-bordered">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Carrier Name</th>
                    <th scope="col">Shipping Method</th>
                    <th scope="col">Service</th>
                    <th scope="col">Transit Days</th>
                    <th scope="col">Est.Delivery</th>
                    <th scope="col">Quote</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="result-content-priority">
            <h5 class="tt-result">Priority</h5>
            <table class="table table-bordered">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Carrier Name</th>
                    <th scope="col">Shipping Method</th>
                    <th scope="col">Service</th>
                    <th scope="col">Transit Days</th>
                    <th scope="col">Est.Delivery</th>
                    <th scope="col">Quote</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="result-content-fc">
            <h5 class="tt-result">FC</h5>
            <table class="table table-bordered">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Carrier Name</th>
                    <th scope="col">Shipping Method</th>
                    <th scope="col">Service</th>
                    <th scope="col">Transit Days</th>
                    <th scope="col">Est.Delivery</th>
                    <th scope="col">Quote</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </form>
</div>
<div class="modal-footer">
    <form action="{{route('export')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" id="data-export" name="data-export" />
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success" id="bt-export" disabled><i class="fas fa-spinner fa-spin"></i> Loading ...</button>
    </form>
</div>

