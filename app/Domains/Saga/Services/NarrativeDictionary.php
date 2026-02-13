<?php

namespace App\Domains\Saga\Services;

class NarrativeDictionary
{
    /**
     * Arcs: Thematic sequences that follow a logical progression.
     * Each step is meant to follow the previous one.
     */
    public static function getArcs(): array
    {
        return [
            'resistance_cycle' => [
                'name' => 'Vòng xoáy Kháng chiến',
                'description' => 'Sự trỗi dậy của lòng tự tôn dân tộc trước áp lực ngoại bang.',
                'steps' => [
                    'Những lời thì thầm về tự do bắt đầu lan tỏa trong các đình làng.',
                    'Dân binh âm thầm luyện tập dưới bóng những rặng tre già.',
                    'Một tiếng hô vang dậy, hiệu lệnh khởi nghĩa đã được phát ra.',
                    'Máu và lửa nhuộm đỏ những cánh đồng, nhưng ý chí không hề lay chuyển.',
                    'Những bước chân hành quân thần tốc làm rung chuyển cả đại địa.',
                    'Khải hoàn ca vang dội, bờ cõi được thu hồi trong niềm tự hào vô hạn.',
                ]
            ],
            'dynastic_cycle' => [
                'name' => 'Hưng suy Triều đại',
                'description' => 'Chu kỳ từ thịnh trị đến suy tàn của một triều đình.',
                'steps' => [
                    'Sắc lệnh mới được ban bố, mở đầu cho một thời kỳ thái bình thịnh trị.',
                    'Kinh đô sầm uất, ngựa xe như nước, áo quần như nêm.',
                    'Những vết nứt đầu tiên xuất hiện trong bộ máy quan liêu.',
                    'Triều đình xa hoa lãng phí trong khi lòng dân bắt đầu ly tán.',
                    'Loạn lạc nổi lên khắp nơi, ánh hào quang cũ chỉ còn là dĩ vãng.',
                ]
            ],
            'monsoon_cycle' => [
                'name' => 'Nhịp sống Giao mùa',
                'description' => 'Sự luân chuyển của thiên nhiên và đời sống nông nghiệp.',
                'steps' => [
                    'Tiếng sấm đầu mùa báo hiệu những cơn mưa rào tưới mát ruộng đồng.',
                    'Nông dân hối hả ra đồng, gieo xuống niềm hy vọng vào một vụ mùa mới.',
                    'Cánh đồng lúa chuyển mình sang màu vàng óng dưới nắng tháng mười.',
                    'Lễ tạ ơn thần nông được tổ chức rộn ràng sau một mùa gặt bội thu.',
                    'Đất đai nghỉ ngơi, chờ đợi một chu kỳ sinh trưởng mới.',
                ]
            ]
        ];
    }

    /**
     * Get all templates organized by category and severity.
     * Severity: 1 (Low), 2 (Medium), 3 (High/Collapse)
     */
    public static function getTemplates(): array
    {
        return [
            'famine_crisis' => [
                1 => [ // Mild
                    'Vụ mùa năm nay kém hơn thường lệ.',
                    'Giá lương thực tăng nhẹ tại các khu chợ.',
                    'Những đợt khô hạn bắt đầu làm người nông dân lo lắng.',
                    'Ngư dân thông báo lưới trống rỗng gần bờ biển.',
                    'Vườn cây ăn quả cho ra những trái nhỏ và chua chát.',
                ],
                2 => [ // Severe
                    'Mất mùa hoàn toàn trên diện rộng.',
                    'Cơn đói bắt đầu lan rộng khắp các vùng quê.',
                    'Lương thực trở nên khan hiếm; người dân than khóc.',
                    'Màu xanh trên cánh đồng khô héo dưới cái nắng gắt.',
                    'Các kho lương thực dự trữ đã cạn kiệt.',
                    'Mùa đông đến sớm, đóng băng những mầm sống cuối cùng.',
                    'Gia súc chết dần vì khát và đói.',
                ],
                3 => [ // Catastrophic
                    'Cơn đói thấu tận xương tủy kìm kẹp vương quốc.',
                    'Họa đói kém hoành hành; người chết không ai chôn cất.',
                    'Một đợt hạn hán kinh hoàng biến đại địa thành bụi cát.',
                    'Làng mạc bị bỏ hoang khi người dân phải ăn rễ cây để sống sót.',
                    'Tiếng đồn về việc ăn thịt người bắt đầu xuất hiện trong bóng tối.',
                    'Đất đai trở nên cằn cỗi; không một mầm xanh nào có thể mọc lại.',
                ]
            ],
            'collapse_warning' => [
                1 => [
                    'Sự bất an bao trùm lấy kinh đô.',
                    'Một vài quan lại nhỏ từ chức để phản đối.',
                    'Tin đồn về sự bất ổn bắt đầu lan truyền.',
                    'Vùng biên viễn dường như đang dần xa rời tầm kiểm soát.',
                ],
                2 => [
                    'Xã hội bắt đầu lung lay tận gốc rễ.',
                    'Sự bất ổn lan rộng trong hệ thống cấp bậc.',
                    'Điềm báo xấu liên tục xuất hiện; các bậc hiền triết lo âu.',
                    'Những rạn nứt lộ rõ trong trật tự xã hội.',
                    'Cấu trúc quyền lực suy yếu thấy rõ.',
                    'Lãnh chúa địa phương bắt đầu phớt lờ sắc lệnh của trung ương.',
                ],
                3 => [
                    'Sự sụp đổ đã cận kề; hồi kết đang hiện hữu.',
                    'Hệ thống rạn nứt và tan vỡ hoàn toàn.',
                    'Hỗn loạn đe dọa nuốt chửng tất cả mọi thứ.',
                    'Sự tan rã tiến gần hơn sau mỗi giờ trôi qua.',
                    'Vô chính phủ trỗi dậy; những luật lệ cũ bị lãng quên.',
                    'Càn khôn điên đảo; trật tự cũ sụp đổ không phanh.',
                ]
            ],
            'social_tension' => [
                1 => [
                    'Sự bất mãn âm ỉ trong các quán trà và hẻm phố.',
                    'Người lao động đình công đòi tăng tiền công.',
                    'Khoảng cách giàu nghèo bắt đầu bị chú ý.',
                    'Tội phạm nhỏ gia tăng tại các thành thị.',
                ],
                2 => [
                    'Sự phẫn nộ tích tụ trong tầng lớp hạ tầng.',
                    'Sự bất ổn sục sôi trong các khu dân cư đông đúc.',
                    'Sự phân hóa giai cấp ngày càng sâu sắc.',
                    'Tầng lớp thượng lưu xa hoa trong khi người nghèo vật lộn.',
                    'Các cuộc biểu tình bùng nổ tại quảng trường chợ.',
                    'Thương nhân bị cướp bóc bởi những đám đông giận dữ.',
                ],
                3 => [
                    'Sự bất bình đẳng đạt đến điểm bùng phát.',
                    'Người dân khao khát đòi lại công lý bằng máu.',
                    'Sự giận dữ bùng nổ thành bạo lực không kiểm soát.',
                    'Căng thẳng dâng cao; tin đồn nội chiến lan rộng.',
                    'Bạo loạn nổ ra; khói lửa bao trùm thành phố.',
                    'Cách mạng được truyền bá công khai trên các đường phố.',
                ]
            ],
            'collective_trauma' => [
                1 => [
                    'Mọi người nói về quá khứ với giọng điệu trầm buồn.',
                    'Các đền thờ cũ được ghé thăm thường xuyên hơn.',
                    'Một cảm giác u buồn thấm đượm trong văn thơ.',
                    'Những cơn ác mộng thường xuyên xuất hiện trong mùa này.',
                ],
                2 => [
                    'Sức nặng của những nỗi kinh hoàng trong quá khứ đè nặng lên tất cả.',
                    'Những vết thương cũ không chịu lành lại.',
                    'Ký ức trở thành một gánh nặng cho người sống.',
                    'Một tâm trạng ảm đạm bao trùm lên toàn dân.',
                    'Lễ hội diễn ra trong sự lặng lẽ và không có niềm vui.',
                    'Bóng ma của quá khứ dường như hiện hữu khắp nơi.',
                ],
                3 => [
                    'Nỗi đau tâm lý vang vọng qua nhiều thế hệ.',
                    'Những vết sẹo lịch sử sâu thêm và rỉ máu.',
                    'Nỗi đau tập thể dâng cao thành sự hoảng loạn.',
                    'Quá khứ ám ảnh người sống; sự điên rồ lan rộng.',
                    'Mọi người mang theo gánh nặng của nỗi buồn không thể chịu nổi.',
                    'Tang tóc trở thành nghi lễ duy nhất còn sót lại.',
                ]
            ],
            'foreign_pressure' => [
                1 => [
                    'Người lạ xuất hiện gần biên giới.',
                    'Các cuộc đàm phán ngoại giao trở nên căng thẳng.',
                    'Giao thương với các lân bang chậm lại.',
                    'Tin đồn về thám báo ngoại bang lan truyền.',
                ],
                2 => [
                    'Các thế lực bên ngoài gây sức ép lên biên giới.',
                    'Mối đe dọa từ ngoại bang ngày một lớn.',
                    'Thế giới bên ngoài trở nên thù địch.',
                    'Các cường quốc ngoại bang xâm lấn vùng biên giới.',
                    'Thám mã báo cáo về các đội quân đang tập kết.',
                    'Các tuyến đường thương mại bị cắt đứt.',
                ],
                3 => [
                    'Mối nguy đang tiến gần từ phía chân trời.',
                    'Kẻ thù tập hợp trước cổng thành.',
                    'Vương quốc đối mặt với hiểm họa diệt vong từ bên ngoài.',
                    'Cuộc xâm lăng dường như đã cận kề và không thể ngăn cản.',
                    'Mối đe dọa tăng dần đến mức áp đảo hoàn toàn.',
                    'Cờ hiệu của ngoại bang xuất hiện ngay trong lòng vương quốc.',
                ]
            ],
            'sudden_change' => [
                1 => [
                    'Một trào lưu mới lan rộng khắp triều đình.',
                    'Một ngôi sao lạ xuất hiện trên bầu trời.',
                    'Một nhà thuyết giáo đầy lôi cuốn xuất hiện.',
                    'Những công cụ mới lạ xuất hiện trên thị trường.',
                ],
                2 => [
                    'Sự thay đổi ập đến mà không có cảnh báo.',
                    'Một bước ngoặt bất ngờ thay đổi cuộc sống hàng ngày.',
                    'Một khám phá mới làm rung chuyển các học giả.',
                    'Một người lạ mang đến những phong cách mới lạ.',
                    'Những lối sống cũ bắt đầu bị đặt dấu hỏi.',
                ],
                3 => [
                    'Một sự chuyển dịch bất ngờ phá vỡ trật tự cũ.',
                    'Thế giới thay đổi hoàn toàn chỉ trong chớp mắt.',
                    'Thực tại biến đổi đột ngột; không còn gì giống như trước.',
                    'Một sự kiện long trời lở đất làm thay đổi diện mạo thế giới.',
                    'Kỷ nguyên cũ kết thúc trong một ánh chớp rực rỡ.',
                ]
            ],
            'discovery' => [
                1 => [
                    'Một thương buôn tìm thấy bản đồ cũ trong nhà kho.',
                    'Những đứa trẻ tìm thấy một lối đi bí mật dưới giếng làng.',
                    'Một bia đá lạ được khai quật khi nông dân cày ruộng.',
                    'Tin đồn về kho báu thất lạc thu hút vài kẻ tò mò.',
                ],
                2 => [
                    'Một di tích cổ đại lộ ra sau trận lở đất.',
                    'Các học giả giải mã được một văn tự bị cấm đoán.',
                    'Một hang động chứa đầy sách cổ được tìm thấy trong rừng sâu.',
                    'Dấu vết của một nền văn minh đã mất xuất hiện tại biên giới.',
                    'Một loại khoáng sản mới có tính chất kỳ lạ được tìm thấy.',
                ],
                3 => [
                    'Cánh cổng dẫn đến một chiều không gian khác đã được mở ra.',
                    'Lăng mộ của vị vua đầu tiên đã bị phá vỡ phong ấn.',
                    'Một thành phố bay lơ lửng được phát hiện sau lớp mây mù.',
                    'Bí mật về nguồn gốc thế giới đã bị phơi bày.',
                    'Một thánh vật có khả năng thay đổi quy luật vật lý đã tái xuất.',
                ]
            ],
            'betrayal' => [
                1 => [
                    'Một lời hứa bị phá vỡ giữa những người bạn cũ.',
                    'Tin đồn về sự không trung thực lan truyền trong hội thương nhân.',
                    'Một quan chức nhỏ bị bắt vì nhận hối lộ.',
                    'Lòng tin bị sứt mẻ trong nội bộ gia tộc.',
                ],
                2 => [
                    'Một tướng quân bí mật liên lạc với quân địch.',
                    'Kế hoạch tác chiến bị rò rỉ ra ngoài.',
                    'Một vụ ám sát nhắm vào nhân vật quan trọng nhưng thất bại.',
                    'Người thân cận nhất của lãnh chúa quay lưng lại với ngài.',
                    'Những đồng minh lâu năm bỗng nhiên trở mặt thành thù.',
                ],
                3 => [
                    'Hoàng đế bị chính cận vệ của mình ám sát.',
                    'Cửa thành mở toang đón quân địch vào trong đêm.',
                    'Một cuộc đảo chính đẫm máu nhuộm đỏ hoàng cung.',
                    'Toàn bộ hội đồng trưởng lão bị đầu độc trong yến tiệc.',
                    'Kẻ được tin tưởng nhất chính là kẻ chủ mưu sự sụp đổ.',
                ]
            ],
            'festival' => [
                1 => [
                    'Lồng đèn đỏ treo cao khắp các con phố.',
                    'Tiếng nhạc vui tươi vang lên từ quảng trường.',
                    'Trẻ em chạy nhảy vui đùa trong bộ quần áo mới.',
                    'Hương thơm của bánh nướng lan tỏa khắp nơi.',
                ],
                2 => [
                    'Một lễ hội lớn được tổ chức để ăn mừng chiến thắng.',
                    'Pháo hoa thắp sáng bầu trời đêm rực rỡ.',
                    'Các đoàn kịch từ khắp nơi đổ về biểu diễn.',
                    'Rượu chảy như suối; tiếng cười nói không ngớt.',
                    'Mọi hận thù tạm thời được gác lại trong ngày vui.',
                ],
                3 => [
                    'Đại lễ trăm năm có một làm rung chuyển cả kinh đô.',
                    'Thần linh giáng thế ban phước trong buổi lễ tế trời.',
                    'Một bữa tiệc xa hoa đến mức đi vào sử sách.',
                    'Toàn bộ vương quốc chìm trong men say hạnh phúc.',
                    'Ngày hội non sông thống nhất, muôn dân reo hò.',
                ]
            ],
            'scientific_breakthrough' => [
                1 => [
                    'Các học giả tranh luận sôi nổi về một lý thuyết mới.',
                    'Một công cụ đo lường tinh xảo được giới thiệu tại triều đình.',
                    'Những bản vẽ kỹ thuật mới lạ xuất hiện trong các xưởng thợ.',
                    'Các nhà giả kim thuật tuyên bố một bước tiến nhỏ.',
                ],
                2 => [
                    'Một phát minh mới thay đổi cách người dân canh tác.',
                    'Học viện hoàng gia công bố bản đồ chi tiết của bầu trời.',
                    'Thuốc súng hoặc một dạng năng lượng mới được thuần hóa.',
                    'Kiến trúc sư xây dựng được những vòm mái không cần cột đỡ.',
                    'Y thuật đạt được bước tiến lớn; tuổi thọ trung bình tăng lên.',
                ],
                3 => [
                    'Sự thật về vũ trụ được phơi bày, làm rung chuyển các tín điều cũ.',
                    'Một cỗ máy khổng lồ thay thế sức lao động của hàng nghìn người.',
                    'Con người bắt đầu chinh phục bầu trời bằng những cỗ máy bay.',
                    'Ranh giới giữa ma thuật và công nghệ bị xóa nhòa.',
                    'Kỷ nguyên khai sáng bắt đầu; bóng tối của sự ngu dốt bị đẩy lùi.',
                ]
            ],
            'religious_schism' => [
                1 => [
                    'Những lời thì thầm về sự thay đổi lan truyền trong các đền thờ.',
                    'Một giáo phái nhỏ bắt đầu ra giảng những điều lạ lùng.',
                    'Các tăng lữ tranh cãi về ý nghĩa của một đoạn kinh cổ.',
                ],
                2 => [
                    'Một vị giám mục tuyên bố ly khai khỏi giáo hội trung ương.',
                    'Những cuộc biểu tình tôn giáo nổ ra tại các quảng trường lớn.',
                    'Tượng thẩn bị đập phá bởi những kẻ cuồng tín.',
                    'Hai phe phái tôn giáo xung đột công khai trên đường phố.',
                ],
                3 => [
                    'Thánh chiến bùng nổ; máu nhuộm đỏ các thánh đường.',
                    'Giáo hội cũ sụp đổ; một trật tự thần quyền mới trỗi dậy.',
                    'Vương quốc bị chia cắt bởi đức tin; anh em tàn sát lẫn nhau.',
                    'Một "Đấng Tiên Tri" mới được tôn sùng như thần thánh sống.',
                ]
            ],
            'cultural_renaissance' => [
                1 => [
                    'Các quán thơ ca mọc lên khắp nơi.',
                    'Trang phục của người dân trở nên sặc sỡ và tinh tế hơn.',
                    'Âm nhạc đường phố trở nên phong phú và đa dạng.',
                ],
                2 => [
                    'Những kiệt tác hội họa ra đời, ca ngợi vẻ đẹp con người.',
                    'Triết học nở rộ; các trường phái tư tưởng tranh đua.',
                    'Kiến trúc đạt đến đỉnh cao của sự hài hòa và tráng lệ.',
                    'Văn học phát triển rực rỡ; chữ viết được phổ cập.',
                ],
                3 => [
                    'Một kỷ nguyên vàng son của nghệ thuật và trí tuệ.',
                    'Tên tuổi của các đại thi hào sẽ được lưu danh muôn thuở.',
                    'Vẻ đẹp và chân lý trở thành lẽ sống của toàn xã hội.',
                    'Nhân loại chạm tay vào sự hoàn mỹ của thần linh.',
                ]
            ],
            'resource_crisis' => [
                1 => [
                    'Nước sạch trở nên khan hiếm tại các giếng làng.',
                    'Rừng bị chặt phá quá mức; gỗ trở nên đắt đỏ.',
                    'Các mỏ khoáng sản bắt đầu có dấu hiệu cạn kiệt.',
                ],
                2 => [
                    'Cuộc chiến tranh giành nguồn nước nổ ra giữa các làng.',
                    'Đất đai bạc màu; không còn canh tác được.',
                    'Khan hiếm nhiên liệu khiến mùa đông trở nên chết chóc.',
                    'Muối và sắt trở thành những mặt hàng xa xỉ phẩm.',
                ],
                3 => [
                    'Hệ sinh thái sụp đổ; thiên nhiên quay lại trừng phạt con người.',
                    'Đại địa trơ trọi; không còn gì để khai thác.',
                    'Nền văn minh lụi tàn vì không còn năng lượng để duy trì.',
                    'Con người quay lại thời kỳ đồ đá vì cạn kiệt tài nguyên.',
                ]
            ],
            'merchant_uprising' => [
                1 => [
                    'Các thương nhân bắt đầu phô trương sự giàu có của mình.',
                    'Hội thương gia yêu cầu có tiếng nói hơn trong triều đình.',
                    'Tiền tệ mới bắt đầu được lưu hành bởi các phường hội.',
                ],
                2 => [
                    'Các thành phố ven biển tuyên bố tự trị về kinh tế.',
                    'Thương nhân từ chối nộp thuế cho triều đình.',
                    'Khống chế các tuyến đường vận chuyển lương thực, các thương nhân nắm giữ huyết mạch vương quốc.',
                ],
                3 => [
                    'Tầng lớp thương nhân lật đổ chính quyền cũ, thiết lập chế độ tài phiệt.',
                    'Đồng tiền thay thế thanh kiếm để cai trị đại địa.',
                    'Các trung tâm thương mại trở thành những pháo đài quyền lực mới.',
                ]
            ],
            'nobility_collapse' => [
                1 => [
                    'Các gia tộc quý tộc bắt đầu tranh giành những vùng đất cằn cỗi.',
                    'Lễ nghi cung đình bị cắt giảm vì thiếu kinh phí.',
                ],
                2 => [
                    'Nhiều dòng họ lâu đời lâm vào cảnh nợ nần và suy vong.',
                    'Lãnh chúa không còn khả năng bảo vệ thần dân của mình.',
                    'Các lâu đài cổ bị bỏ hoang hoặc bị cướp bóc.',
                ],
                3 => [
                    'Trật tự phong kiến sụp đổ hoàn toàn; quý tộc chỉ còn là cái danh hão.',
                    'Gia phả hoàng tộc bị đốt sạch trong những cuộc nổi loạn.',
                    'Sự kế thừa máu mủ bị xóa bỏ bởi làn sóng của thời đại mới.',
                ]
            ],
            'warrior_dominance' => [
                1 => [
                    'Binh lính xuất hiện nhiều hơn tại các ngã đường.',
                    'Võ quan bắt đầu nắm giữ các vị trí hành chính quan trọng.',
                ],
                2 => [
                    'Thiết quân luật được ban bố tại nhiều tỉnh thành.',
                    'Ngân sách vương quốc đổ dồn vào việc rèn đúc vũ khí.',
                    'Tiếng gươm đao át tiếng kinh cầu và thi ca.',
                ],
                3 => [
                    'Các tướng lĩnh quân đội nắm quyền tối cao; xã hội biến thành một trại lính khổng lồ.',
                    'Luật pháp được viết bằng lưỡi kiếm và máu.',
                    'Toàn bộ vương quốc bị cuốn vào vòng xoáy chiến tranh liên miên.',
                ]
            ],
            'default' => [
                1 => [
                    'Một năm yên tĩnh trôi qua.',
                    'Các mùa luân chuyển nhẹ nhàng.',
                    'Cuộc sống vẫn tiếp diễn bình thản trong các thôn xóm.',
                    'Một vụ mùa bội thu được tổ chức ăn mừng.',
                    'Trẻ em vui đùa hồn nhiên trên các đường phố.',
                    'Mưa thuận gió hòa, lòng dân an định.',
                    'Tiếng chuông chùa ngân vang trong buổi chiều tà.',
                    'Những cánh cò bay lả bay la trên đồng ruộng.',
                ],
                2 => [
                    'Thế giới vẫn tiếp tục vận hành theo quỹ đạo của nó.',
                    'Thời gian trôi qua; vương quốc vẫn đứng vững.',
                    'Một kỷ nguyên khác mở ra trong sự bình an.',
                    'Chu kỳ luân hồi tiếp tục không bị phá vỡ.',
                    'Sự thay đổi chậm rãi nhưng chắc chắn đang được cảm nhận.',
                    'Các bậc trưởng bối kể những câu chuyện cổ cho hậu thế.',
                    'Lịch sử được ghi chép vào những cuộn thư phủ bụi thời gian.',
                    'Dòng chảy của định mệnh cuộn cuộn không ngừng.',
                ],
                3 => [
                    'Hòa bình ngự trị, dù còn mong manh.',
                    'Vương quốc đứng vững trước sự tàn phá của thời gian.',
                    'Một thời kỳ hoàng kim dường như đang mở ra.',
                    'Sự thịnh vượng chạm đến cả những người nghèo khổ nhất.',
                    'Thần linh dường như mỉm cười với đại địa này.',
                    'Giang sơn sừng sững, xã tắc vững bền qua muôn đời.',
                ]
            ],
            // --- VIETNAMESE CULTURAL BEATS ---
            'vn_intros' => [
                'architectural' => [
                    'Dưới mái đình rêu phong cổ kính,',
                    'Bên cạnh những dòng sông đỏ nặng phù sa,',
                    'Trong sự tĩnh lặng của những ngôi chùa cổ,',
                    'Đứng trước cổng làng rêu phong theo năm tháng,',
                    'Giữa những dãy núi đá vôi trùng điệp,',
                ],
                'philosophical' => [
                    'Tuân theo đạo lý của tổ tiên để lại,',
                    'Trong sự hài hòa giữa con người và thiên nhiên,',
                    'Khi những lời sấm truyền cũ dần ứng nghiệm,',
                    'Dưới sự chứng kiến của anh linh các bậc tiền nhân,',
                    'Thấm nhuần tư tưởng lấy dân làm gốc,',
                ]
            ],
            'vn_sensory' => [
                'Hương hoa cau thơm nồng nàn trong gió đêm.',
                'Tiếng ve kêu ran ran báo hiệu một mùa hè rực lửa.',
                'Màu vàng óng của những cánh đồng lúa chín trải dài vô tận.',
                'Không khí mang theo vị mặn mòi của biển cả và gió ngàn.',
                'Tiếng gãy gọn của những khóm tre đen trong đêm vắng.',
                'Khói bếp tỏa ra từ những mái nhà tranh, mang theo mùi nếp mới.',
                'Ánh trăng tan vào dòng nước, lấp lánh như dát bạc.',
            ],
            'vn_environmental' => [
                'Những rặng tre già nghiêng mình trong nắng chiều.',
                'Dòng Mekong cuộn chảy, mang theo nhựa sống của đại địa.',
                'Núi rừng Tây Bắc mờ ảo trong làn sương sớm.',
                'Những con thuyền lênh đênh trên mặt nước phẳng lặng.',
                'Mưa rào mùa hạ bất chợt đổ xuống, gội sạch bụi trần.',
                'Gió biển thổi vào, mang theo sự tươi mát và sức sống.',
            ],
        ];
    }

    /**
     * Get a random template for a category and severity.
     */
    public static function getRandomTemplate(string $category, int $severityScore): string
    {
        $templates = self::getTemplates();
        $categoryTemplates = $templates[$category] ?? $templates['default'];

        // Map severity score (0-100) to levels 1, 2, 3
        $level = match(true) {
            $severityScore >= 8 => 3,
            $severityScore >= 4 => 2,
            default => 1,
        };

        // Fallback to lower levels if current level empty (unlikely with this dict)
        $options = $categoryTemplates[$level] ?? $categoryTemplates[2] ?? $categoryTemplates[1];

        return $options[array_rand($options)];
    }
}
