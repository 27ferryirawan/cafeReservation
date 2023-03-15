<!DOCTYPE html>
<html>
    <head>
        <title>Reservation</title>
    </head>
    <body>
        <header>
            @include('layouts/navbar')
        </header>
        <main>
            <div style="display: flex; flex-direction: column; margin: 20px 50px 20px 80px; font-size: 17px;">
                <label style="font-weight: bold;">Table</label>     
                <div style="display: flex; flex-direction: row; margin-top: 5px;">
                    <!-- status 0 = available, 1 = on reserve, 2 = guest in  -->
                    <div style="display: flex; flex-direction: row;"> 
                        <div style="width: 25px; height: 25px; border: 1px solid black; background-color: rgba(0, 255, 0, 0.1)"></div>
                        <label style="margin-left: 5px;">Available</label>     
                    </div>
                    <div style="display: flex; flex-direction: row; margin-left: 20px;"> 
                        <div style="width: 25px; height: 25px; border: 1px solid black; background-color: rgba(255, 255, 0, 0.3)"></div>
                        <label style="margin-left: 5px;">Booked</label>     
                    </div>
                    <div style="display: flex; flex-direction: row; margin-left: 20px;"> 
                        <div style="width: 25px; height: 25px; border: 1px solid black; background-color: rgba(255, 0, 0, 0.3)"></div>
                        <label style="margin-left: 5px;">Guest</label>     
                    </div>
                </div>
                <div style="margin: 20px 50px 0px 0px; ">    
                    <canvas id="mapCanvas"></canvas>
                </div>
                <div style="display: flex; flex-direction: row; align-items: center;"> 
                    <label style="font-weight: bold;">Reservation</label>
                    <button style="padding:0px 25px; height: 35px; border-radius: 25px; background-color: #392A23; border: none; color: white; margin-left: 10px" onclick="downloadReport()">Download Report</button>
                </div>
                <table class="reserv-tab"style="width: 100%;">
                    <tr>
                        <td style="width: 21%;">Name</td>
                        <td style="width: 14%">Table</td>
                        <td style="width: 17%">Date</td>
                        <td style="width: 17%">Fee</td>
                        <td style="width: 17%">Status</td>
                        <td style="width: 14%; text-align: center">Action</td>
                    </tr>
                    @foreach($reservations as $data)
                        <tr">
                            <td>
                                <div style="display: flex; flex-direction: row; line-height: 1.2"> 
                                    <img style="width: 45px; height: 45px; border-radius: 45px;" src="storage/{{ $data->profile_picture }}"/>
                                    <div style="display: flex; flex-direction: column; margin-left: 5px"> 
                                        <label style="margin-left: 5px;"> {{ $data->name }} </label>     
                                        <label style="margin-left: 5px;"> {{ $data->email }} </label>     
                                    </div>
                                </div>
                            </td>
                            <td>{{ $data->table_name }}</td>
                            <td>{{ date('d M Y h:i', strtotime($data->reservation_date)); }}</td>
                            <td>Rp {{ number_format($data->fee, 0, ',', '.') }},00</td>
                            <td>{{ $data->payment_status }}</td>
                            <td>
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <button style="width: 125px; height: 35px; border-radius: 25px; background-color: #392A23; border: none; color: white" data-resid="{{$data->id}}" onclick="showUpdateModal(this)">Update</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
                <div style="display: flex; flex-direction: row; margin-top: 10px"> 
                    <label style="margin-left: 35%; width: 17%"> 
                        Total Fee
                    </label>
                    <label> 
                        Rp {{ number_format($totalFee, 0, ',', '.') }},00
                    </label>
                </div>
            </div> 
            <div id="modal" class="modal">
                <div class="modal-content">
                    <h2>Update Reservation</h2>
                    <label style="font-size: 17px; margin: 15px 0px 0px 3px">Status</label>
                    <select id="statusDropdown" style="height: 35px; border: 1px black solid; margin: 0px 3px 45px 3px;">
                        <option value="" disabled>Select Status</option>
                        <option value="1">On Reserve</option>
                        <option value="2">Guest In</option>
                        <option value="3">Guest Out</option>
                        <option value="4">Cancel Reservation</option>
                    </select>
                    <button class="confirm-button" onclick="updateResStatus()">Update</button>
                    <button class="cancel-button" onclick="hideUpdateModal()">Cancel</button>
                </div>
            </div>
            <div id="successOrFailedModal" class="modal">
                <div class="modal-content">
                    <p id="successOrFailedText" class="ajax-label"></p>
                    <button class="confirm-button" onclick="hideSuccessOrFailedModal()">Confirm</button>
                </div>
            </div>
        </main>
        @include('layouts/footer')      
        @include('popper::assets')
    </body>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</html>

<script>

    const canvas = document.getElementById('mapCanvas');
    const ctx = canvas.getContext('2d');
    var tableDetail = {!! $tableDetail !!}; 
    const modal = document.getElementById("modal");
    const successOrFailedModal = document.getElementById("successOrFailedModal");
    var resId = 0;

    var isTableSelected = [
        {table: "out1",  isSelected: false, x: 8, y: 102.9, width: 10.5, height: 12.5},
        {table: "out2",  isSelected: false, x: 8, y: 79.3, width: 10.5, height: 12.5},
        {table: "out3",  isSelected: false, x: 8, y: 56.2, width: 10.5, height: 12.5},
        {table: "out4",  isSelected: false, x: 8, y: 32.7, width: 10.5, height: 12.5},
        {table: "out5",  isSelected: false, x: 64.8, y: 23.3, width: 10.5, height: 12.5},
        {table: "out6",  isSelected: false, x: 85.2, y: 23.3, width: 10.5, height: 12.5},
        {table: "out7",  isSelected: false, x: 101.6, y: 59.2, width: 10.5, height: 12.5},
        {table: "out8",  isSelected: false, x: 101.6, y: 86.2, width: 10.5, height: 12.5},
        {table: "out9",  isSelected: false, x: 90.5, y: 113.2, width: 10.8, height: 12.5},
        {table: "out10", isSelected: false, x: 70.5, y: 113.2, width: 10.5, height: 12.5},
        {table: "out11", isSelected: false, x: 50.4, y: 113.2, width: 10.5, height: 12.5},
        {table: "out12", isSelected: false, x: 30.3, y: 113.2, width: 10.5, height: 12.5},
        {table: "long1", isSelected: false, x: 243, y: 79.6, width: 11, height: 42.5},
        {table: "long2", isSelected: false, x: 121.3, y: 26.3, width: 35, height: 14},
        {table: "long3", isSelected: false, x: 70.2, y: 69.3, width: 25.5, height: 14},
        {table: "long4", isSelected: false, x: 37.2, y: 69.3, width: 25.5, height: 14},
        {table: "sofa1", isSelected: false, x: 261.6, y: 79.6, width: 11, height: 42.5},
        {table: "sofa2", isSelected: false, x: 121.3, y: 113, width: 35.3, height: 14},
        {table: "in6",   isSelected: false, x: 161.9, y: 23.1, width: 10.7, height: 12.9},
        {table: "in5",   isSelected: false, x: 176.2, y: 23.1, width: 10.7, height: 12.9},
        {table: "in4",   isSelected: false, x: 190.9, y: 23.1, width: 10.7, height: 12.9},
        {table: "in3",   isSelected: false, x: 205.1, y: 23.1, width: 10.7, height: 12.9},
        {table: "in2",   isSelected: false, x: 219.6, y: 23.1, width: 10.7, height: 12.9},
        {table: "in1",   isSelected: false, x: 234.2, y: 23.1, width: 10.7, height: 12.9},
    ];

    function clearAll(firstInit){
        
        if(firstInit){
            for (var i = 0; i < tableDetail.length; i++) {
                if(tableDetail[i].id == isTableSelected.find(table => table.table == tableDetail[i].id).table){
                    isTableSelected.find(table => table.table == tableDetail[i].id).tableName = tableDetail[i].table_name;
                    isTableSelected.find(table => table.table == tableDetail[i].id).price = tableDetail[i].price;
                    isTableSelected.find(table => table.table == tableDetail[i].id).status = tableDetail[i].status;
                    drawOrRemoveSelected(tableDetail[i].id, true);
                }
            }
        }
        else{
            // ctx.clearRect(0, 0, canvas.width, canvas.height);
            // getTableDetailData();
            location.reload();
        }
    }

    clearAll(true);

    function drawOrRemoveSelected(tableId, firstInit) {
        var isSelected = isTableSelected.find(table => table.table == tableId).isSelected;
        var isAvailable = isTableSelected.find(table => table.table == tableId).status == 0 ? true : false;
        
        if(!firstInit){
            if(!isSelected){
                var tableData = isTableSelected.find(table => table.table == tableId)
                var xTab = tableData.x
                var yTab = tableData.y
                var widthTab = tableData.width
                var heightTab = tableData.height
                // status 0 = available
                ctx.fillStyle = "rgba(0, 255, 0, 0.4)";
                ctx.fillRect(xTab, yTab, widthTab, heightTab);
                isTableSelected.find(table => table.table == tableId).isSelected = true;
                
            } else {
                var tableData = isTableSelected.find(table => table.table == tableId)
                var xTab = tableData.x - 2
                var yTab = tableData.y - 2 
                var widthTab = tableData.width + 4
                var heightTab = tableData.height + 4
                ctx.clearRect(xTab, yTab, widthTab, heightTab);
                isTableSelected.find(table => table.table == tableId).isSelected = false;
            }
            if(reservationDateValue.length != 0 && reservationTimeValue.length != 0 )
            ketReservasiChange();
        } else if(firstInit){
            var tableData = isTableSelected.find(table => table.table == tableId)
            var xTab = tableData.x
            var yTab = tableData.y
            var widthTab = tableData.width
            var heightTab = tableData.height
            // 1 = on reserve, 2 = guest in
            if(isTableSelected.find(table => table.table == tableId).status == 1){
                ctx.fillStyle = "rgba(255, 255, 0, 0.3)"; 
            } else if(isTableSelected.find(table => table.table == tableId).status == 2){
                ctx.fillStyle = "rgba(255, 0, 0, 0.3)"; 
            } else if(isTableSelected.find(table => table.table == tableId).status == 0){
                ctx.fillStyle = "rgba(0, 255, 0, 0.1)"; 
            }
            ctx.fillRect(xTab, yTab, widthTab, heightTab);
        }
    }

    function getTableDetailData() {
        $.ajax({
            url: '/reservation/getTableDetailData', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                for (var i = 0; i < data.length; i++) {
                    if(data[i].id == isTableSelected.find(table => table.table == data[i].id).table){
                        isTableSelected.find(table => table.table == tableDetail[i].id).status = data[i].status;
                        isTableSelected.find(table => table.table == tableDetail[i].id).isSelected = false
                        drawOrRemoveSelected(data[i].id, true);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to get tableDetail data:', errorThrown);
            }
        });
    }

    function showUpdateModal(button) {
        resId = button.dataset.resid
        modal.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function updateResStatus(){
        var statusDropdown = document.getElementById("statusDropdown");
        var selectedValue = statusDropdown.value;
        showSuccessOrFailedModal()
    }

    function showSuccessOrFailedModal() {
        successOrFailedModal.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function hideSuccessOrFailedModal() {
        successOrFailedModal.style.display = "none";
        document.body.style.overflow = "auto";
        clearAll(false);
    }

    function hideUpdateModal() {
        modal.style.display = "none";
        document.body.style.overflow = "auto";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            hideUpdateModal();
        }
    }

</script>

<style>
    .reserv-tab td{
        border-bottom: 1px black solid;
    }
    .reserv-tab tr > td {
        padding: 10px 0px;
    }
</style>