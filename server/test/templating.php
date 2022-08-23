<?php require_once dirname(__FILE__, 2) . "/user/header.php"; ?>


<div class="row mb-3">
    <div class="col-md-6 offset-md-3 col-sm-12">
        <div class="card">
            <div class="row no-gutters">

                <div class="col-1" style="background-color:#d3d3d3;">
                    <div id="campaign-1" style="height: 100%; width: 100%;"></div>
                </div>

                <div class="col-8">
                    <div class="card-body">

                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" value="{{username}}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="session_time">Session Time:</label>
                            <input type="text" class="form-control" id="session_time" value="{{session_time}}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="">Quota Out:</label>
                            <input type="text" class="form-control" id="" value="{{quota_out}}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="">Quota In:</label>
                            <input type="text" class="form-control" id="" value="{{quota_in}}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="">Activated On:</label>
                            <input type="text" class="form-control" id="" value="{{date_activate}}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="">Expired On:</label>
                            <input type="text" class="form-control" id="" value="{{date_expiry}}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="">Status:</label>
                            <input type="text" class="form-control" id="" value="{{status}}" disabled>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<? require_once dirname(__FILE__, 2) . "/user/footer.php"; ?>

