<div class="row" style="margin: 18px;">

    <div class="card">
    <div class="card-header text-white bg-info">
        <?php 
            # https://developer.wordpress.org/reference/functions/wp_get_current_user/
            $user_info = wp_get_current_user(); 
            #var_dump($user_info);
            #die(); # debug OK
        ?>
        <h3>Welcome to Plugin Moving Forward <?php echo ucfirst( $user_info->user_login ); ?></h3>
    </div>
        <div class="card-body">

            <table class="table">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Firstname</th>
                    <th scope="col">Email</th>
                    <th scope="col">Id Card Verifier</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                # https://kinsta.com/fr/blog/personnaliser-menu-wordpress/
                $users = get_users( array( 'orderby' => 'nicename' ) );
                #var_dump($users); die(); # debug OK
                foreach( $users as $user ) {
                ?>
                    <tr>
                        <th scope="row"><?php echo ( $user->ID ); ?></th>
                        <td><?php echo ucfirst( $user->user_nicename ); ?></td>
                        <td><a href="mailto:<?php echo ( $user->user_email ); ?>"><?php echo ( $user->user_email ); ?></a></td>
                        <td>
                            <form action="/wp-json/wp/v2/idverifier/<?php echo ( $user->ID ); ?>" method="post">
                                <?php
                                    # https://developer.wordpress.org/reference/functions/get_user_meta/
                                    #get_user_meta( int $user_id, string $key = '', bool $single = false )
                                    $user_description = get_user_meta( $user->ID, $key='description', false );
                                    //var_dump($user_description); #die(); # debug OK
                                ?>
                                <select class="form-select-sm" name="selectidcardverifier" aria-label="Id card verifier">
                                    <option selected><?php echo ucfirst(($user_description)[0]); ?></option>
                                    <option value="none">None</option>
                                    <option value="canbe">Can be</option>
                                    <option value="yes">Yes</option>
                                </select>
                                <input type="hidden" value="<?php $user->ID ?>">
                                <button type="submit" class="btn btn-outline-success btn-sm" name="submit">Submit</button>
                            </form>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>

        </div><!--/card-body-->
    </div>

</div>



