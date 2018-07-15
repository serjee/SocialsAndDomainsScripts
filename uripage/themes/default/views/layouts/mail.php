<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type">
</head>
<table width="802" border="0" cellspacing="0" cellpadding="0" align="center" style="font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;">
    <tr>
        <td bgcolor="#d4d4d4" valign="top" style="padding-top:1px; padding-bottom:1px;">

            <table width="800" border="0" cellspacing="0" cellpadding="0" align="center" style="font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif; padding:5px;" bgcolor="#FFFFFF">
                <tr>
                    <td width="400"><a href="http://<?=Yii::t('main', 'siteurl');?>/" style="font-size:22px;font-weight:bold;color:#444;text-decoration:none;"><?php echo CHtml::encode(Yii::app()->name); ?></a></td>
                    <td align="right"><p style="font-size:18px;color:#999;"><strong><?=Yii::app()->params['adminEmail'];?></strong></td>
                </tr>
            </table>

            <table width="800" border="0" cellspacing="0" cellpadding="0" align="center" style="font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;" bgcolor="#FFFFFF">
                <tr>
                    <td width="10" bgcolor="#668d1f" align="center" valign="middle"><a href="http://<?=Yii::t('main', 'siteurl');?>/"><img src="me.png" width="80" height="80" /></a></td>
                    <td bgcolor="#668d1f" align="left" valign="middle"><p style="font-size:24px; color:#FFF;"><?php if(isset($data['description'])) echo $data['description']; ?></strong></p></td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:10px;" valign="top">
                        <?php echo $content ?>
                        <p style="padding-bottom:10px;"></p>
                    </td>
                </tr>
                <tr>
                    <td width="10" bgcolor="#668d1f" align="center" valign="middle"></td>
                    <td bgcolor="#668d1f" align="left" valign="middle"><p style="font-size:24px; color:#FFF;"></p></td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>