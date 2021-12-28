<?php
defined('BASEPATH') or exit('No direct script access allowed');
class CommentModel  extends AbstractModel
{
  var $_table = 'tb_comment';
  var $_pk = "comment_id";
  var $_sort = "comment_id";
}
