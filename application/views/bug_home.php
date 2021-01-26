<div id="contentwrapper">
<div id="contentcolumn">
    <h1>Bug / Feature Tracker</h1>
    <br />
    <p>
        Welcome to the bug and feature tracker for Succession Wars.
    </p>
    <p>
        This is the place to create, comment on, and view bugs and feature requests for the game. 
        Every tracker has a karma value which is voted on by the users of this site.  Want a new 
        feature to come into being sooner?  Up-vote it.  Rather see it at the bottom of the programming barrel?  Down-vote it.
    </p>
    <p>
        This will allow the programmers to prioritize their efforts to fix the most important bugs and implement the most wanted features.
    </p>
    <div class="box2">
        <h3>Bug / Feature Submission</h3>
        <br />
        <p>Found an error or a bug?  Thought of a cool new option, feature, or addition?  Create a new tracker here!</p>
        <ul>
            <li class="first">
                <?php echo anchor('bugtracker/create', 'Create a New Tracker'); ?>
            </li>
        </ul>
    </div>
    <div class="box3">
        <h3>Contact Me</h3>
        <br />
        <p>
            I'm always available via email for issues that need attention right away.
            I try to respond to personal request within 24 hours if at all possible.
        </p>
        <p>
            Email me at: brian@scrapyardarmory.com
        </p>
        
    </div>
</div>
</div>

<?php $this->load->view('bug_sidebars'); ?>
