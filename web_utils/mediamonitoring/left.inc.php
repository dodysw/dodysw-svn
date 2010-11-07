            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber5">
              <tr>
                <td width="100%" bgcolor="#E4E4E4">
                <p style="margin-left: 10; margin-right: 10"></td>
              </tr>
              <tr>
                <td width="100%">
                <p class=form_title align="center"><b><br>
                Archieve
                by Category</b><font face="Arial"><br>
&nbsp;</font></td>
              </tr>
              <tr>


                <td width="100%">
<form action="news_arch.php">
<p align="center" style="margin-left: 10; margin-right: 10">
<font face="Arial">

<!-- start form archive by cat -->
<? $cat = instantiate_module('news_cat'); $cat->show_combo('cat',$cat->enum_list(),1,1) ?>
<!-- end form archive by cat -->
<input type="submit" value="Submit" name="B1">
</form>
</font>

</td>


              </tr>
              <tr>

<!-- start Stats Title -->
                <td width="100%" bgcolor="#F2F2F2">
                <p>
                <a href="http://media.interactive.web.id/stats.php">Statistics</a><br></td>
<!-- end Stats Title -->

              </tr>
              <tr>
                <td width="100%" bgcolor="#F2F2F2"><br></td>
              </tr>
              <tr>

<!-- start Search Title -->
                <td width="100%" bgcolor="#E4E4E4">
                <p class=form_title align="center"><b><br>
                Search<br>
&nbsp;</b></td>
<!-- end Search Title -->

              </tr>
              <tr>

                <td width="100%" bgcolor="#E4E4E4">
<!-- start Search form -->
<? $news = instantiate_module('news'); $news->fe_search_form() ?>

<!-- end Search form -->
&nbsp;</td>


              </tr>
              </table>
