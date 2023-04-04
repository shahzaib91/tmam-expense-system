<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Transactions List</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 40px;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    Merchant Transactions
                </div>
            </div>
            <div>
                <table border="1" cellpadding="5" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Merchant</th>
                            <th>Country</th>
                            <th>Merchant Amount</th>
                            <th>Transaction Amount</th>
                            <th>Date/Time</th>
                            <th>Sync Status</th>
                            <th>Sync Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($transactions->count()>0)

                            @foreach($transactions as $t)

                            <tr>
                                <td>{{ $t->transaction_id }}</td>
                                <td>{{ $t->transaction_type == 'd' ? 'Debit' : 'Credit' }}</td>
                                <td>{{ (int) $t->transaction_status == 0 ? 'Declined' : 'Authorized' }}</td>
                                <td>{{ $t->merchant_name }}</td>
                                <td>{{ $t->merchant_country }}</td>
                                <td>{{ $t->merchant_currency.' '.number_format($t->amount, 2) }}</td>
                                <td>{{ $t->transaction_currency.' '.number_format($t->transaction_amount, 2) }}</td>
                                <td>{{ $t->transaction_datetime }}</td>
                                <td>{{ (int) $t->is_synced == 0 ? 'No' : 'Yes' }}</td>
                                <td>{{ $t->sync_response == '' ? '-' : $t->sync_response }}</td>
                            </tr>

                            @endforeach

                        @else
                            <tr>
                                <td colspan="10" style="text-align: center">
                                    No data to display!
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
