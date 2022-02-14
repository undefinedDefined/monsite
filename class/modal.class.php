<?php


class Modal{

    private $header;
    private $content;

    public function __construct(
        string $newHeader,
        string $newContent = ''
    )
    {
        if(!empty($newHeader)){
            $this->header = htmlspecialchars($newHeader);
        }else{
            throw new Exception('Le header ne peut pas être vide');
        }

        (!empty($newContent)) ? $this->content = htmlspecialchars($newContent) : '';

    }

    public function formModal(){
        $print = '<div class="ui coupled modal">';
        $print .= '<i class="close icon"></i>';
        $print .= '<div class="header">';
        $print .= $this->header;
        $print .= '</div>'; 
        $print .= '<div class="content">';
        (!is_null($this->content)) ? $print .= $this->content : '';
        $print .= '</div>';
        $print .= '<div class="actions">';
        $print .= '<div class="ui close button">Annuler</div>';
        $print .= '<button class="ui primary right labeled icon submit button">';
        $print .= '<i class="right arrow icon"></i> Modifier';
        $print .= '</button>';
        $print .= '</div>';
        $print .= '</div>';
       
        return $print;
    }
}