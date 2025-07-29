@extends('admin_panel.layout.app')

@section('content')
          <div class="main-content">
            <div class="main-content-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 stretched_card">
                            <div class="card bg-card-success">
                                <div class="card-body">
                                    <p class="card_title text-white"><i class="fa fa-rupee-sign mr-2"></i> Total Revenue
                                        <span class="pull-right text-bold">100K</span></p>
                                </div>
                                <div class="text-center">
                                    <div id="total_revenue" class="sparklines-full">
                                        <canvas width="240" height="60"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 stretched_card mt-mob-4">
                            <div class="card bg-card-primary">
                                <div class="card-body">
                                    <p class="card_title text-white"><i class="feather ft-activity mr-2"></i> Daily Sales <span class="pull-right text-bold">20K</span></p>
                                </div>
                                <div class="text-center">
                                    <div id="daily_sales" class="sparklines-full">
                                        <canvas width="240" height="60"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 stretched_card mt-mob-4">
                            <div class="card bg-card-danger">
                                <div class="card-body">
                                    <p class="card_title text-white"><i class="feather ft-hard-drive mr-2"></i> Yesterday Sales
                                        <span class="pull-right text-bold">30K</span></p>
                                </div>
                                <div class="text-center">
                                    <div id="yesterday_revenue" class="sparklines-full">
                                        <canvas width="240" height="60"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8 stretched_card mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card_title">Sales By Profit</h4>
                                    <div id="morris_line"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mt-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <a href="#" class="mb-2 d-block">Many desktop publishing</a>
                                    <p>
                                        It is a long established fact that a reader will be distracted by the readable content will be distracted by the readable content.
                                    </p>
                                    <div class="float-left">
                                        <span class="badge badge-warning mt-1">In-progress</span>
                                    </div>
                                    <div class="float-right">
                                        <a href="#" class="btn btn-primary">View</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <a href="#" class="mb-2 d-block">Contrary to popular belief</a>
                                    <p>
                                        It is a long established fact that a reader will be distracted by the readable contentwill be distracted by the readable content.
                                    </p>
                                    <div class="float-left">
                                        <span class="badge badge-success mt-1">Completed</span>
                                    </div>
                                    <div class="float-right">
                                        <a href="#" class="btn btn-primary">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-5 stretched_card mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card_title mb-4">Stats by Sale</h4>
                                    <div class="chart_container">
                                        <canvas id="average-sales" width="40" class="chartjs-render-monitor"></canvas>
                                    </div>
                                    <div class="stats_list mt-4">
                                        <p>
                                            <i class="feather ft-activity mr-2 text-primary"></i> Total Revenue
                                            <span class="float-right">Rs23K</span>
                                        </p>
                                        <p>
                                            <i class="feather ft-activity mr-2 text-danger"></i> Revenue from Social
                                            <span class="float-right">Rs10K</span>
                                        </p>
                                        <p>
                                            <i class="feather ft-activity mr-2 text-success"></i> Paid Campagin
                                            <span class="float-right">Rs12K</span>
                                        </p>
                                        <p class="mb-0">
                                            <i class="feather ft-activity mr-2"></i> Digital Marketing
                                            <span class="float-right">Rs9K</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- data table -->
                        <div class="col-lg-7 mt-4 stretched_card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card_title">Registered Users</h4>
                                    <div class="table-responsive">
                                        <table id="dataTable">
                                            <thead class="bg-light text-capitalize">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Staus</th>
                                                    <th>Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Airi Satou</td>
                                                    <td><span class="text-warning">●</span> <span class="badge badge-warning">In Progress</span></td>
                                                    <td>60%</td>
                                                </tr>
                                                <tr>
                                                    <td>Angelica Ramos</td>
                                                    <td><span class="text-success">●</span> <span class="badge badge-success">Completed</span></td>
                                                    <td>100%</td>
                                                </tr>
                                                <tr>
                                                    <td>Ashton Cox</td>
                                                    <td><span class="text-danger">●</span> <span class="badge badge-danger">Not Completed</span></td>
                                                    <td>75%</td>
                                                </tr>
                                                <tr>
                                                    <td>Bradley Greer</td>
                                                    <td><span class="text-info">●</span> <span class="badge badge-info">Please Check</span></td>
                                                    <td>10%</td>
                                                </tr>
                                                <tr>
                                                    <td>Brenden Wagner</td>
                                                    <td><span class="text-success">●</span> <span class="badge badge-success">Completed</span></td>
                                                    <td>100%</td>
                                                </tr>
                                                <tr>
                                                    <td>Caesar Vance</td>
                                                    <td><span class="text-primary">●</span> <span class="badge badge-primary">Worked</span></td>
                                                    <td>100%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- data table -->
                    </div>
                    <div class="row">
                        <div class="col-lg-7 stretched_card mt-4">
                            <div class="card">
                                <h5 class="card-header"> Sales By Traffic</h5>
                                <div class="card-body p-0">
                                    <ul class="traffic-sales list-group list-group-flush">
                                        <li class="traffic-sales-content list-group-item ">
                                            <span class="traffic-sales-name">Direct</span>
                                            <span class="traffic-sales-amount">Rs4000.00
                                        <span class="icon-circle-small icon-box-xs text-success ml-4 bg-success-light">
                                            <i class="fa fa-fw fa-arrow-up"></i>
                                        </span>
                                            <span class="ml-1 text-success">5.86%</span>
                                            </span>
                                        </li>
                                        <li class="traffic-sales-content list-group-item">
                                            <span class="traffic-sales-name">Search
                                        <span class="traffic-sales-amount">Rs3123.00
                                            <span class="icon-circle-small icon-box-xs text-success ml-4 bg-success-light">
                                                <i class="fa fa-fw fa-arrow-up"></i>
                                            </span>
                                            <span class="ml-1 text-success">5.86%</span>
                                            </span>
                                            </span>
                                        </li>
                                        <li class="traffic-sales-content list-group-item">
                                            <span class="traffic-sales-name">Social
                                        <span class="traffic-sales-amount ">Rs3099.00
                                            <span class="icon-circle-small icon-box-xs text-success ml-4 bg-success-light">
                                                <i class="fa fa-fw fa-arrow-up"></i>
                                            </span>
                                            <span class="ml-1 text-success">5.86%</span>
                                            </span>
                                            </span>
                                        </li>
                                        <li class="traffic-sales-content list-group-item">
                                            <span class="traffic-sales-name">Referrals
                                        <span class="traffic-sales-amount ">Rs2220.00
                                            <span class="icon-circle-small icon-box-xs text-danger ml-4 bg-danger-light">
                                                <i class="fa fa-fw fa-arrow-down"></i>
                                            </span>
                                            <span class="ml-1 text-danger">4.02%</span>
                                            </span>
                                            </span>
                                        </li>
                                        <li class="traffic-sales-content list-group-item ">
                                            <span class="traffic-sales-name">Email
                                        <span class="traffic-sales-amount">Rs1567.00
                                            <span class="icon-circle-small icon-box-xs text-danger ml-4 bg-danger-light">
                                                <i class="fa fa-fw fa-arrow-down"></i>
                                            </span>
                                            <span class="ml-1 text-danger">3.86%</span>
                                            </span>
                                            </span>
                                        </li>
                                        <li class="traffic-sales-content list-group-item ">
                                            <span class="traffic-sales-name">Visits
                                        <span class="traffic-sales-amount">Rs1567.00
                                            <span class="icon-circle-small icon-box-xs text-danger ml-4 bg-danger-light">
                                                <i class="fa fa-fw fa-arrow-down"></i>
                                            </span>
                                            <span class="ml-1 text-danger">3.86%</span>
                                            </span>
                                            </span>
                                        </li>
                                        <li class="traffic-sales-content list-group-item ">
                                            <span class="traffic-sales-name">Marketing
                                        <span class="traffic-sales-amount">Rs1567.00
                                            <span class="icon-circle-small icon-box-xs text-danger ml-4 bg-danger-light">
                                                <i class="fa fa-fw fa-arrow-down"></i>
                                            </span>
                                            <span class="ml-1 text-danger">3.86%</span>
                                            </span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 mt-4 stretched_card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="dropdown float-right">
                                        <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                        <i class="ion-ios-more-outline"></i>
                                    </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="javascript:void(0);" class="dropdown-item">Weekly Report</a>
                                            <a href="javascript:void(0);" class="dropdown-item">Monthly Report</a>
                                            <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                            <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                        </div>
                                    </div>
                                    <h4 class="card_title mb-3">Recent Activities</h4>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-hover mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="media recent_activity mt-2">
                                                            <img class="mr-3 rounded-circle" src="images/team/member1.jpg" width="50" alt="Activity Image">
                                                            <div class="media-body">
                                                                <h6 class="mt-0 mb-1">Jhon Doe
                                                                    <small class="font-weight-normal d-block mt-1">18 Jan 2019 11:28
                                                                pm
                                                            </small>
                                                                </h6>
                                                                <span class="mt-2 d-block">Many desktop publishing ...</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="table-action text-center">
                                                        <div class="dropdown">
                                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                        <i class="ion-ios-more-outline"></i>
                                                    </a>
                                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end">
                                                                <!-- item-->
                                                                <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                                                <!-- item-->
                                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="media recent_activity mt-2">
                                                            <img class="mr-3 rounded-circle" src="images/team/member1.jpg" width="50" alt="Activity Image">
                                                            <div class="media-body">
                                                                <h6 class="mt-0 mb-1">David Ron
                                                                    <small class="font-weight-normal d-block mt-1">18 Jan 2019 11:28
                                                                pm
                                                            </small>
                                                                </h6>
                                                                <span class="mt-2 d-block">Many desktop publishing ...</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="table-action text-center">
                                                        <div class="dropdown">
                                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                        <i class="ion-ios-more-outline"></i>
                                                    </a>
                                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end">
                                                                <!-- item-->
                                                                <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                                                <!-- item-->
                                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="media recent_activity mt-2">
                                                            <img class="mr-3 rounded-circle" src="images/team/member1.jpg" width="50" alt="Activity Image">
                                                            <div class="media-body">
                                                                <h6 class="mt-0 mb-1">Mike Hussey
                                                                    <small class="font-weight-normal d-block mt-1">18 Jan 2019 11:28
                                                                pm
                                                            </small>
                                                                </h6>
                                                                <span class="mt-2 d-block">Many desktop publishing ...</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="table-action text-center">
                                                        <div class="dropdown">
                                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                        <i class="ion-ios-more-outline"></i>
                                                    </a>
                                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end">
                                                                <!-- item-->
                                                                <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                                                <!-- item-->
                                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- end table-responsive-->

                                </div>
                                <!-- end card body-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection