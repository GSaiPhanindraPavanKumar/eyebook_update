<?php 
include 'sidebar.php';
?>
<div class="main-panel">        
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-6 grid-margin stretch-card mx-auto">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Change Password</h4>
                  <form class="forms-sample" action="change_password" method="post">
                    <div class="form-group row">
                      <label for="currentPassword" class="col-sm-3 col-form-label">Current Password</label>
                      <div class="col-sm-9">
                        <input type="password" class="form-control" id="currentPassword" placeholder="Current Password" name="currentPassword">
                      </div>
                    </div>
                    <div class="form-group row">
                        <label for="newPassword" class="col-sm-3 col-form-label">New Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="exampleInputPassword2" placeholder="New Password" name="newPassword">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="confirmPassword" class="col-sm-3 col-form-label">Re-Type Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" name="confirmPassword">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    <button class="btn btn-light" type="button" onclick="window.location.href='profile'">Cancel</button>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!-- content-wrapper ends -->
<?php 
include 'footer.html';
?>