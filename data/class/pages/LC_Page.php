<?php
/*
 * Copyright(c) 2000-2007 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 */

/**
 * Web Page を制御する基底クラス
 *
 * Web Page を制御する Page クラスは必ずこのクラスを継承する.
 * PHP4 ではこのような抽象クラスを作っても継承先で何でもできてしまうため、
 * あまり意味がないが、アーキテクトを統一するために作っておく.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page {

    // {{{ properties

    /** メインテンプレート */
    var $tpl_mainpage;

    /** メインナンバー */
    var $tpl_mainno;

    /** CSS のパス */
    var $tpl_css;

    /** タイトル */
    var $tpl_title;

    /** カテゴリ */
    var $tpl_page_category;

    /**
     * 安全に POST するための URL
     */
    var $postURL;

    /**
     * このページで使用する遷移先
     */
    var $transitions;

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->postURL = $_SERVER['PHP_SELF'];
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {}

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {}

    /**
     * 遷移元が自サイトかどうかチェックする.
     *
     * 遷移元が自サイト以外の場合はエラーページへ遷移する.
     *
     * @return void
     */
    function checkPreviousURI() {
    }

    /**
     * 指定の URL へリダイレクトする.
     *
     * リダイレクト先 URL に SITE_URL 及び SSL_URL を含むかチェックし,
     * LC_Page::getToken() の値を URLパラメータで自動的に付与する.
     *
     * @param string $url リダイレクト先 URL
     * @return void|boolean $url に SITE_URL 及び, SSL_URL を含まない場合 false,
     * 						 正常に遷移可能な場合は, $url の URL へ遷移する.
     */
    function sendRedirect($url) {

        if (preg_match("/(" . preg_quote(SITE_URL, '/')
                          . "|" . preg_quote(SSL_URL, '/') . ")/", $url)) {

            header("Location: " . $url . "?" . TRANSACTION_ID_NAME . "=" . $this->getToken());
        }
        return false;
    }

    // }}}
    // {{{ protected functions

    /**
     * トランザクショントークンを生成し, 取得する.
     *
     * 悪意のある不正な画面遷移を防止するため, 予測困難な文字列を生成して返す.
     * 同時に, この文字列をセッションに保存する.
     *
     * この関数を使用するためには, 生成した文字列を次画面へ渡すパラメータとして
     * 出力する必要がある.
     *
     * 例)
     * <input type="hidden" name="transactionid" value="この関数の返り値" />
     *
     * 遷移先のページで, LC_Page::isValidToken() の返り値をチェックすることにより,
     * 画面遷移の妥当性が確認できる.
     *
     * @return string トランザクショントークンの文字列
     */
    function getToken() {
        $token = $this->createToken();
        $_SESSION[TRANSACTION_ID_NAME] = $token;
        return $token;
    }

    /**
     * トランザクショントークンの妥当性をチェックする.
     *
     * 前画面で生成されたトランザクショントークンの妥当性をチェックする.
     * この関数を使用するためには, 前画面のページクラスで LC_Page::getToken()
     * を呼んでおく必要がある.
     *
     * @return boolean トランザクショントークンが有効な場合 true
     */
    function isValidToken() {

        $checkToken = "";

        // $_POST の値を優先する
        if (isset($_POST[TRANSACTION_ID_NAME])) {

            $checkToken = $_POST[TRANSACTION_ID_NAME];
        } elseif (isset($_GET[TRANSACTION_ID_NAME])) {

            $checkToken = $_GET[TRANSACTION_ID_NAME];
        }

        $ret = false;
        // token の妥当性チェック
        if ($checkToken === $_SESSION[TRANSACTION_ID_NAME]) {

            $ret = true;
        }

        unset($_SESSION[TRANSACTION_ID_NAME]);

        return $ret;
    }

    // }}}
    // {{{ private functions

    /**
     * トランザクショントークン用の予測困難な文字列を生成して返す.
     *
     * @return string トランザクショントークン用の文字列
     */
    function createToken() {
        return sha1(uniqid(rand(), true));
    }
}
?>
