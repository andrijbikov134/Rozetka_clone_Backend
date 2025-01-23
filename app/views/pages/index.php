<div class="flexContainerRow">
    <div>
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Type</th>
                <th>SSN</th>
                <th></th>
            </tr>
            <?php 
            {
                foreach ($users as $user)
                {
                    echo "<tr>";
                    echo "<td>{$user['name']}</td>" . "<td><a href=\"\">{$user['email']}</a></td>" . 
                    "<td class=\"color_{$user['status']}\">{$user['status']}</td>" . "<td>{$user['type']}</td>" .
                    "<td>{$user['ssn']}</td>"
                    . "<td><a class=\"deleteButton\" href=\"index.php?action=remove&id={$user['id']}\">remove</a></td>";
                    echo "</tr>";  
                }  
            }
            ?>

        </table>
        <div class="flexContainerRow">
            <div class="buttonLoadFromApi">
                <a class="loadFromApiButton" href="index.php?action=loadfromapi">Load from API all</a>
            </div>
            <div class="buttonRemoveAll">
                <a class="removeAllButton" href="index.php?action=removeallusers">Remove all</a>
            </div>
        </div>
    </div>

    <div>
        <form class="formFlex" action="index.php">
            <input type="text" name="name" placeholder="Name">
            <input type="text" name="email" placeholder="Email">
            <select name="status">
                <option value="active">Active</option>
                <option value="not active">Not active</option>
            </select>
            <select name="type">
                <option value="publisher">Publisher</option>
                <option value="writer">Writer</option>
                <option value="moderator">Moderator</option>
            </select>
            <input type="text" name="ssn" placeholder="SSN">
            <input type="hidden" name="action" value="create">
            <input class="formFlexButtonAdd" type="submit" value="Add manually">
        </form>
    </div>
</div>
