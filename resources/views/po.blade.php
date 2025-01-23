<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            position: relative;
            width: 100%;
            margin-bottom: 30px;
        }

        .title {
            color: #00bcd4;
            font-size: 28pt;
            display: inline-block;
        }

        /* Divider */
        .divider {
            border-bottom: 2px solid #00bcd4;
            margin: 20px 0;
            clear: both;
        }

        /* Info Sections */
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }

        .left-section {
            float: left;
            width: 60%;
        }

        .right-section {
            float: right;
            width: 35%;
        }

        /* Company Info */
        .company-info, .ship-to, .supplier-info {
            margin-bottom: 20px;
        }

        h3 {
            font-size: 14pt;
            margin-bottom: 5px;
        }

        .address {
            font-style: italic;
            color: #666;
            margin-bottom: 10px;
        }

        /* Order Details */
        .order-details {
            width: 100%;
            margin-bottom: 20px;
        }

        .order-details dt {
            font-weight: bold;
        }

        .order-details dd {
            font-weight: normal;
            font-style: italic;
            color: #666;
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            clear: both;
        }

        .table th {
            background-color: #00bcd4;
            color: white;
            text-align: left;
            padding: 8px;
            font-size: 12pt;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11pt;
        }

        /* Totals */
        .totals {
            width: 300px;
            float: right;
            margin-top: 20px;
        }

        .totals-row {
            padding: 5px 0;
            clear: both;
        }

        .totals-row span:first-child {
            float: left;
        }

        .totals-row span:last-child {
            float: right;
        }

        .total {
            color: #00bcd4;
            font-weight: bold;
            border-top: 2px solid #00bcd4;
            margin-top: 5px;
            padding-top: 5px;
        }

        /* Clearfix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="clearfix header">
            <h1 class="title">Purchase Order</h1>
        </div>

        <div class="divider"></div>

        <div class="clearfix info-section">
            <div class="left-section">
                <div class="company-info">
                    <h3>CV. Basama Jaya Pratama</h3>
                    <div class="address">
                        Jl. Arjuno No. 36<br>
                        Kota Malang, 65119<br>
                        Phone: 08632736278
                    </div>
                </div>

                <div class="supplier-info">
                    <h3>Supplier</h3>
                    <div>{{$data->nama_supplier}}</div>
                    <div class="address">
                        {{$supplier->alamat}}<br>
                        Phone: {{$supplier->no_hp1}}<br>
                        Email: {{$supplier->email}}
                    </div>
                    <div>Terms: 30 days</div>
                </div>
            </div>

            <div class="right-section">
                <dl class="order-details">
                    <dt>PO no:</dt>
                    <dd>{{$data->kode_po}}</dd>
                    <dt>Purchase Order Status:</dt>
                    <dd>{{$data->status}}</dd>
                    <dt>Date:</dt>
                    <dd>{{$data->created_at}}</dd>
                    <dt>Requested By:</dt>
                    <dd>{{$data->created_by}}</dd>
                    <dt>Approved By:</dt>
                    <dd>{{$data->approved_by}}</dd>
                </dl>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Kode Barcode</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Isi Barang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->details as $item)
                <tr>
                    <td>{{$item->kode_barcode}}</td>
                    <td>{{$item->nama_barang}}</td>
                    <td>{{$item->qty}}</td>
                    <td>{{$item->nama_satuan}}</td>
                    <td>{{$item->isi}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>