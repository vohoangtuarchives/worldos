<?php

namespace App\Domains\Saga\Author;

class AuthorRegistry
{
    /** @var array<string, AuthorPersona> */
    private array $personas = [];

    public function __construct()
    {
        $this->registerDefaultPersonas();
    }

    private function registerDefaultPersonas(): void
    {
        // 1. Wuxia Master (Kim Dung Style)
        $this->register(new AuthorPersona(
            name: 'WuxiaMaster',
            tone: 'grand_oriental',
            vocabularyMap: [
                'world' => 'Giang Hồ',
                'leader' => 'Minh Chủ',
                'rebel' => 'Nghịch Tặc',
                'conflict' => 'Ân Oán',
                'magic' => 'Thần Thông',
                'army' => 'Thiết Binh',
                'famine' => 'Họa Đói Kém',
                'peace' => 'Thịnh Thế',
                'death' => 'Vẫn Lạc',
                'victory' => 'Đắc Thắng',
                'defeat' => 'Bại Tẩu',
                'mountain' => 'Thanh Sơn',
                'river' => 'Linh Giang',
                'city' => 'Hùng Thành',
            ],
            introStyles: [
                "Trên thế gian này, phàm là chuyện gì cũng có nhân quả.",
                "Giang hồ dậy sóng, anh hùng hào kiệt khắp nơi đều cảm nhận được chân khí đang biến đổi.",
                "Trời cao lồng lồng, thưa mà khó lọt, một kịch bản mới lại bắt đầu.",
                "Đao kiếm vô tình, giang hồ vốn là nơi của những kẻ không sợ chết.",
                "Vạn vật tuần hoàn, thịnh cực ắt suy, đó là lẽ tự nhiên của trời đất.",
            ],
            bridgingPhrases: [
                "Thực ra, đây chỉ là khởi đầu của một trường hạo kiếp.",
                "Trong cái rủi có cái may, trong cái họa có cái phúc.",
                "Có những chuyện, người tính không bằng trời tính.",
                "Họa phúc khôn lường, ai hay đâu chữ ngờ.",
            ],
            signatureFlourishes: [
                "Kiếm khí tung hoành ba vạn dặm.",
                "Một ý niệm định sinh tử.",
                "Chân khí cuộn trào như đại hải.",
                "Nhất bộ nhất sát, máu nhuộm trường y.",
                "Hào khí ngất trời, chấn động bát hoang.",
            ],
            descriptors: [
                'sensory' => [
                    'famine_crisis' => ['Hương vị của bụi trần và đất chết thấm vào từng hơi thở.', 'Ruộng đồng nứt nẻ, như những vết thương rỉ máu của đại địa.'],
                    'social_tension' => ['Chân khí trong không gian trở nên hỗn loạn, báo hiệu điều chẳng lành.', 'Tiếng đao kiếm chạm nhau vang lên lanh lảnh trong đêm vắng.'],
                ],
                'atmosphere' => [
                    'famine_crisis' => ['Một luồng tử khí bao trùm khắp xóm làng.', 'Thiên địa không nhân từ, coi vạn vật như chó rơm.'],
                    'social_tension' => ['Sát khí đặc quánh khiến người ta nghẹt thở.', 'Vận mệnh giang hồ đang chuyển mình trong cơn bão dữ.'],
                ]
            ]
        ));

        // 2. Dark Historian (GrimDark Style)
        $this->register(new AuthorPersona(
            name: 'DarkHistorian',
            tone: 'cynical_dark',
            vocabularyMap: [
                'world' => 'Vùng Đất Chết',
                'peace' => 'Sự Yên Lặng Giả Tạo',
                'hero' => 'Kẻ Sống Sót',
                'hope' => 'Sự Ảo Tưởng',
                'gods' => 'Những Thực Thể Vô Cảm',
                'love' => 'Sự Phản Bội Sắp Tới',
                'victory' => 'Sự Tàn Sát Có Mục Đích',
                'order' => 'Sự Áp Bức Tối Thượng',
                'nature' => 'Sự Hoang Phế Độc Địa',
            ],
            introStyles: [
                "Máu đã đổ xuống lớp bụi dày của lịch sử.",
                "Chẳng có ai chiến thắng trong cuộc chơi của những vị thần điên loạn.",
                "Hy vọng là một căn bệnh, và cái chết là liều thuốc duy nhất.",
                "Định mệnh là một trò đùa dai của những kẻ cầm quyền vô hình.",
                "Sự thật luôn bị chôn vùi dưới những nấm mồ không tên.",
            ],
            bridgingPhrases: [
                "Chẳng có gì thay đổi, ngoại trừ cái tên của những kẻ cai trị.",
                "Bình minh chỉ mang đến sự rõ ràng cho những thảm kịch.",
                "Chúng ta đều chỉ là những quân cờ trong một bàn cờ đã định sẵn cái chết.",
            ],
            signatureFlourishes: [
                "Hư vô đang gào thét.",
                "Tro tàn của những giấc mơ.",
                "Cái giá của sự tồn tại là nỗi đau không dứt.",
                "Bóng tối không bao giờ kết thúc.",
            ],
            descriptors: [
                'sensory' => [
                    'famine_crisis' => ['Mùi thối rữa của những giấc mơ chết khô.', 'Tiếng bụng đói gào thét trong sự im lặng của thần linh.'],
                    'social_tension' => ['Mùi máu tanh nồng nặc trong từng hơi thở của phố thị.', 'Tiếng gào thét của kẻ bị lãng quên.'],
                ],
                'atmosphere' => [
                    'famine_crisis' => ['Một bóng tối lạnh lẽo nuốt chửng những tia hy vọng cuối cùng.', 'Thế giới này vốn dĩ đã hỏng hóc từ lâu.'],
                    'social_tension' => ['Sự căm thù sục sôi như nham thạch dưới lòng đất.', 'Không có nơi nào để trốn chạy khỏi sự tàn ác.'],
                ]
            ]
        ));

        // 3. Epic Bard (Lord of the Rings Style)
        $this->register(new AuthorPersona(
            name: 'EpicBard',
            tone: 'noble_high_fantasy',
            vocabularyMap: [
                'world' => 'Trung Địa',
                'king' => 'Đại Đế',
                'enemy' => 'Bóng Tối Phía Bắc',
                'war' => 'Đại Chiến Kỷ Nguyên',
                'forest' => 'Khu Rừng Thần Bí',
                'sea' => 'Biển Lớn Vô Tận',
                'old' => 'Cổ Xưa',
                'holy' => 'Thánh Khiết',
            ],
            introStyles: [
                "Mọi chuyện bắt đầu từ khi những đại thụ cổ xưa vẫn còn biết hát.",
                "Ánh sáng của các vì sao đã tiên đoán về ngày hôm nay.",
                "Từ thuở sơ khai, khi thế giới còn non trẻ và đầy phép thuật.",
                "Trong những trang sách cổ của thời đại thứ nhất, nó đã được ghi lại.",
            ],
            signatureFlourishes: [
                "Ánh sáng sẽ luôn tìm thấy con đường của nó.",
                "Vinh quang thuộc về những kẻ quả cảm.",
                "Khúc ca của thời đại vẫn còn vang vọng.",
            ],
            descriptors: [
                'sensory' => [
                    'famine_crisis' => ['Mặt đất rên rỉ dưới sức nặng của nỗi buồn.', 'Ánh mặt trời gay gắt như sự trừng phạt của các vì sao.'],
                    'social_tension' => ['Tiếng kèn trận vang vọng từ phía xa xăm.', 'Bóng đen huyền bí bao phủ lấy những ngọn tháp cao.'],
                ],
                'atmosphere' => [
                    'famine_crisis' => ['Một kỷ nguyên xám xịt đang dần chiếm lấy ánh sáng.', 'Sự thử thách cuối cùng cho lòng dũng cảm.'],
                    'social_tension' => ['Nhịp đập của định mệnh vang vọng trong gió.', 'Vinh quang và nỗi đau đan xen vào nhau.'],
                ]
            ]
        ));
    }

    public function register(AuthorPersona $persona): void
    {
        $this->personas[$persona->name] = $persona;
    }

    public function get(string $name): ?AuthorPersona
    {
        return $this->personas[$name] ?? null;
    }

    public function all(): array
    {
        return $this->personas;
    }
}
