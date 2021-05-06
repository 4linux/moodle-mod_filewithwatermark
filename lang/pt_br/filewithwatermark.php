<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for component 'filewithwatermark', language 'pt_br'
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['cannotgeneratewatermark'] = 'Não foi possível gerar a marca d\'água';
$string['clicktodownload'] = 'Clique no link {$a} para baixar o arquivo.';
$string['clicktoopen2'] = 'Clique no link {$a} para visualizar o arquivo.';
$string['configdisplayoptions'] = 'Selecione todas as opções que devem estar disponíveis, as configurações existentes não são modificadas. Segure a tecla CTRL para selecionar vários campos.';
$string['configframesize'] = 'Quando uma página da web ou um arquivo carregado é exibido dentro de um quadro, este valor é a altura (em pixels) do quadro superior (que contém a navegação).';
$string['configpopupheight'] = 'Qual deve ser a altura padrão para novas janelas pop-up?';
$string['displayoptions'] = 'Opções de exibição disponíveis';
$string['displayselect'] = 'Exibição';
$string['displayselect_help'] = 'Essa configuração, junto com o tipo de arquivo e se o navegador permitir a incorporação, determina como o arquivo é exibido. As opções podem incluir:
* Automático - A melhor opção de exibição para o tipo de arquivo é selecionada automaticamente
* Incorporar - O arquivo é exibido na página abaixo da barra de navegação junto com a descrição do arquivo e quaisquer blocos
* Forçar download - O usuário é solicitado a baixar o arquivo
* Abrir - apenas o arquivo é exibido na janela do navegador
* Em pop-up - O arquivo é exibido em uma nova janela do navegador sem menus ou barra de endereço
* In frame - O arquivo é exibido dentro de um frame abaixo da barra de navegação e da descrição do arquivo';
$string['displayselectexplain'] = 'Escolha o tipo de exibição; infelizmente, nem todos os tipos são adequados para todos os arquivos.';
$string['description'] = "Descrição";
$string['filenotfound'] = 'Arquivo não encontrado, desculpe.';
$string['filterfiles'] = 'Use filtros no conteúdo do arquivo';
$string['filterfilesexplain'] = 'Selecione o tipo de filtragem de conteúdo de arquivo. Observe que isso pode causar problemas para alguns miniaplicativos Flash e Java. Certifique-se de que todos os arquivos de texto estão em codificação UTF-8.';
$string['framesize'] = 'Altura do quadro';

/** Module info */
$string['modifieddate'] = 'Modificado {$a}';
$string['modulename'] = 'Arquivo com marca d\'água';
$string['modulename_help'] = 'O modulo de arquivo com marca d\'água permite ao professor prover um arquivo que conterá a marca d\'água com os dados do usuário como um recurso ao curso. Os estudantes poderão fazer o download do arquivo. O arquivo deve ter extensão PDF.';
$string['modulenameplural'] = 'Arquivos com marca d\'água';
$string['name'] = "Nome";
$string['notmigrated'] = 'Este tipo de recurso legado ({$a}) ainda não foi migrado, desculpe.';
$string['pluginadministration'] = 'Administração módulo de arquivo com marca d\'água';
$string['pluginname'] = "Arquivo com marca d\'água";
$string['popupheight'] = 'Altura do pop-up (em pixels)';
$string['popupheightexplain'] = 'Especifica a altura padrão das janelas pop-up.';
$string['popupwidth'] = 'Largura do pop-up (em pixels)';
$string['popupwidthexplain'] = 'Especifica a largura padrão das janelas pop-up.';
$string['printintro'] = 'Exibir descrição do recurso';
$string['printintroexplain'] = 'Exibir a descrição do recurso abaixo do conteúdo? Alguns tipos de exibição podem não exibir a descrição, mesmo se ativados.';
$string['requiredfiles'] = 'Arquivo necessário';
$string['selectfiles'] = "Selecione o arquivo";
$string['showdate'] = 'Mostrar data de upload / modificação';
$string['showdate_desc'] = 'Exibir data de upload/modificação na página do curso?';
$string['showdate_help'] = 'Exibe a data de upload/modificação ao lado de links para o arquivo.
Se houver vários arquivos neste recurso, a data de modificação/upload do arquivo inicial será exibida.';
$string['showsize'] = 'Mostrar tamanho';
$string['showsize_desc'] = 'Exibir o tamanho do arquivo na página do curso?';
$string['showsize_help'] = 'Exibe o tamanho do arquivo, como \'3,1 MB \', ao lado dos links para o arquivo.
Se houver vários arquivos neste recurso, o tamanho total de todos os arquivos será exibido.';
$string['showtype'] = 'Mostrar tipo';
$string['showtype_desc'] = 'Exibir tipo de arquivo (por exemplo, \'documento do Word \') na página do curso? ';
$string['showtype_help'] = 'Exibe o tipo do arquivo, como \'PDF \', ao lado dos links para o arquivo.
Se houver vários arquivos neste recurso, o tipo de arquivo inicial será exibido.';
$string['uploadeddate'] = 'Carregado {$a}';
$string['versionnotallowed'] = 'Seu PDF está em uma versão maior que 1.4. Versões compatíveis: 1.0, 1.1, 1.2, 1.3, 1.4. Recomendamos o uso da ferramenta <a href="https://docupub.com/pdfconvert">Docupub</a> para alteração da versão.';
$string['filewithwatermark:addinstance'] = 'Adicionar novo arquivo com marca d\'água';
$string['filewithwatermarkdetails_sizedate'] = '{$a->size} {$a->date}';
$string['filewithwatermarkdetails_sizetype'] = '{$a->size} {$a->type}';
$string['filewithwatermarkdetails_sizetypedate'] = '{$a->size} {$a->type} {$a->date}';
$string['filewithwatermarkdetails_typedate'] = '{$a->type} {$a->date}';
$string['filewithwatermark:view'] = 'Visualizar arquivo com marca d\'água';
