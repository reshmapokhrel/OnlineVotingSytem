<?php
@include 'inc/connection.php';
session_start();

if(isset($_POST['add_election'])){

  
        $election_topic = mysqli_real_escape_string($conn, $_POST['election_topic']);
        $number_of_candidates = mysqli_real_escape_string($conn, $_POST['number_of_candidates']);
        $starting_date = mysqli_real_escape_string($conn, $_POST['starting_date']);
        $ending_date = mysqli_real_escape_string($conn, $_POST['ending_date']);
        $inserted_by = $_SESSION['fullname'];
        $inserted_on = date("Y-m-d");


        $date1=date_create($inserted_on);
        $date2=date_create($starting_date);
        $diff=date_diff($date1,$date2);
        
        
        if((int)$diff->format("%R%a") > 0)
        {
            $status = "InActive";
        }else {
            $status = "Active";
        }
        if(empty($election_topic) || empty($number_of_candidates) || empty($starting_date) || empty($ending_date)){
         $message[] = 'please fill out all';
        }
        // inserting into db
       else{ 
         $insert= "INSERT INTO elections(election_topic, no_of_candidates, starting_date, ending_date, status, inserted_by, inserted_on) VALUES('". $election_topic ."', '". $number_of_candidates ."', ' 
       ". $starting_date ."', '". $ending_date ."', '". $status ."', '". $inserted_by ."', '". $inserted_on ."')" ;
         $upload= mysqli_query($conn, $insert);
         if($upload){
            $message[] = 'new election added successfully';
         }else{
            $message[] = 'could not add the election';
          }
          }
 }
   


if(isset($_GET['delete'])){
   $id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM elections WHERE election_id = $id");
   header('location:addelection.php');
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- <title>dashboard</title> -->
  <!-- custome css -->
  <link rel="stylesheet" href="../dashboard/dashboard.css"">
  <!-- For icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"
  rel="stylesheet">
  <link rel="stylesheet" href="../cssfolder/election.css">

<body>
<div class="grid-container">

  <!-- header -->
  <header class="header">
    <div class="menu-icon" onclick="openSidebar()">
      <span class="material-icons-outlined">menu</span>
    </div>
    <div class="class-left">
      <span class="material-icons-outlined"> search </span> 
    </div>
    <div class="class-right">
      <span class="material-icons-outlined" >account_circle </a></span>
      
    </div>
  </header>
  <!-- end header -->

  <!-- slidebar -->
   <aside id="sidebar">
   <div class="sidebar-title">
    <div class="sidebar-brand">
   <a href="dashboard.php"> <span class="material-icons-outlined" style="color:#b0b2bd;">how_to_vote</span> </a> Go Vote
    </div>
    <span class="material-icons-outlined" onclick="closeSidebar()">close </span>
   </div>

   <ul class="sidebar-list">
        <li class="sidebar-list-item">
        <span class="material-icons-outlined">dashboard</span> Dashboard</li>

      <li class="sidebar-list-item">
       <a href="dashboard.php"><span class="material-icons-outlined">event_available</span> Election</li></a>
      
     <li class="sidebar-list-item"><span class="material-icons-outlined">groups</span> Candidates</li>
     <li class="sidebar-list-item"><span class="material-icons-outlined">visibility</span> View Result</li>
   
    <li class="sidebar-list-item"> <span class="material-icons-outlined">settings </span> Setting</li>

   </ul>
  </aside>
  <!-- Endsidebar -->
  <main class="main-container">
    
<?php

if(isset($message)){
   foreach($message as $message){
      echo '<span class="message">'.$message.'</span>';
   }
}

?>
   
<div class="form-container">

   <div class="admin-product-form-container">

   <form action="addelection.php" method="post" enctype="multipart/form-data">

            <h3>add a new election</h3>
            <select class="box" name="election_id" required>
            <option value="">Select Election Topic</option>
            <?php 
                        $fetchingElections = mysqli_query($conn, "SELECT * FROM elections") OR die(mysqli_error($conn));
                        $isAnyElectionAdded = mysqli_num_rows($fetchingElections);
                        if($isAnyElectionAdded > 0)
                        {
                            while($row = mysqli_fetch_assoc($fetchingElections))
                            {
                                $election_id = $row['id'];
                                $election_name = $row['election_topic'];
                                $allowed_candidates = $row['no_of_candidates'];

                                // Now checking how many candidates are added in this election 
                                $fetchingCandidate = mysqli_query($conn, "SELECT * FROM candidate_details WHERE election_id = '". $election_id ."'") or die(mysqli_error($conn));
                                $added_candidates = mysqli_num_rows($fetchingCandidate);

                                if($added_candidates < $allowed_candidates)
                                {
                        ?>
                                <option value="<?php echo $election_id; ?>"><?php echo $election_name; ?></option>
                        <?php
                                }
                            }
                        }else {
                    ?>
                            <option value=""> Please add election first </option>
                    <?php
                        }
                    ?>
            </select>
            <input type="text" placeholder="enter candidate fullname" name="candidate_name" class="box">
            <input type="text" placeholder="enter candidate address" name="candidate_address" class="box">
            <input type="email" placeholder="enter email" name="candidate_email" class="box">

            <input type="file" accept=" image/jpg, image/png, image/jpeg" placeholder="upload the image" name="starting_date" class="box" required>
           
            <textarea name="candidate_bio" placeholder="Short Bio" class="box" cols="0" rows="0"></textarea>
            <input type="submit" class="btn" name="add_election" value="add_election">
         </form>

<?php

      $select = mysqli_query($conn, "SELECT * FROM elections");
   
   ?>
   <div class="product-display">
      <table class="product-display-table">
         <thead>
         <tr>
            <th>S.N</th>
            <th>Phot</th>
            <th>Candidate name</th>
            <th>Address</th>
            <th>Email</th>
            <th>Election</th>
            <th>action</th>
         </tr>
         </thead>
         <?php 
                    $fetchingData = mysqli_query($conn, "SELECT * FROM elections") or die(mysqli_error($conn)); 
                    $isAnyElectionAdded = mysqli_num_rows($fetchingData);

                    if($isAnyElectionAdded > 0)
                    {
                        $sno = 1;
                        while($row = mysqli_fetch_assoc($fetchingData))
                        {
                            $election_id = $row['election_id'];
                ?>
                            <tr>
                                <td><?php echo $sno++; ?></td>
                                <td><?php echo $row['election_topic']; ?></td>
                                <td><?php echo $row['no_of_candidates']; ?></td>
                                <td><?php echo $row['starting_date']; ?></td>
                                <td><?php echo $row['ending_date']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td> 
                                <a href="update.php?edit=<?php echo $row['election_id']; ?>" class="box-btn"> <i class="fas fa-edit"></i> edit </a>
                                <a href="addelection.php?delete=<?php echo $row['election_id']; ?>" class="box-btn"> <i class="fas fa-trash"></i> delete </a>
                                </td>
                            </tr>
                <?php
                        }
                    }else {
            ?>
                        <tr> 
                            <td colspan="7"> No any election is added yet. </td>
                        </tr>
            <?php
                    }
                ?>
   </div>

</div>
  





  </main>
  <!-- end main -->
 


  
 
</div>















<!-- custom js -->
  <script src="../assets/js/dashobrd.js"></script>
  <script src="../assets/js/first.js"></script>
  <script src="../assets/js/drop_down.js"></script>
</body>
</body>
</html>